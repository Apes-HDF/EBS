<?php

declare(strict_types=1);

namespace App\Controller\Payment\Group;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\PaymentToken;
use App\Entity\User;
use App\Message\Command\Payment\DoneCommand;
use App\MessageBus\CommandBusInterface;
use App\Repository\GroupOfferRepository;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(User::ROLE_USER)]
final class DoneAction extends AbstractController
{
    use i18nTrait;
    use FlashTrait;
    use GroupOfferTrait;

    public const ROUTE_NAME = 'app_payment_done';

    public function __construct(
        private readonly GroupOfferRepository $groupOfferRepository,
        private readonly Payum $payum,
        private readonly TranslatorInterface $translator,
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    /**
     * @see DoneCommandHandler
     * @see https://github.com/Payum/Payum/blob/master/docs/symfony/get-it-started.md#payment-is-done
     */
    #[Route(
        path: MyAccountAction::BASE_URL_EN.'/payment/{id}/done',
        name: self::ROUTE_NAME,
        requirements: ['id' => Requirement::UUID_V6],
    )]
    public function __invoke(Request $request, string $id, #[CurrentUser] User $user): Response
    {
        $groupOffer = $this->getGroupOffer($id);

        try {
            /** @var PaymentToken $token */
            $token = $this->payum->getHttpRequestVerifier()->verify($request);
        } catch (\Exception) {
            throw new UnprocessableEntityHttpException('Cannot verify Payum token.');
        }

        /** @var GetHumanStatus $status */
        $status = $this->commandBus->dispatch(new DoneCommand($groupOffer->getId(), $user->getId(), $token));
        if ($status->isCaptured()) {
            $this->addFlashSuccess($this->translator->trans($this->getI18nPrefix().'.flash.success', [
                '%group%' => $groupOffer->getGroup()->getName()],
            ));
            $request->getSession()->remove('payment_in_progress');
        } else {
            $this->addFlashWarning($this->translator->trans($this->getI18nPrefix().'.status.'.$status->getValue()));
        }

        return $this->redirectToRoute('app_group_show', $groupOffer->getGroup()->getRoutingParameters());
    }
}
