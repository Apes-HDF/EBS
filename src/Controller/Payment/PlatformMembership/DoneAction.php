<?php

declare(strict_types=1);

namespace App\Controller\Payment\PlatformMembership;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Entity\PaymentToken;
use App\Entity\PlatformOffer;
use App\Entity\User;
use App\Message\Command\Payment\PlatformMembershipPaidCommand;
use App\MessageBus\CommandBusInterface;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(User::ROLE_USER)]
final class DoneAction extends AbstractController
{
    use i18nTrait;
    use FlashTrait;

    public const ROUTE_NAME = 'app_platform_payment_done';

    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly Payum $payum,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @see https://github.com/Payum/Payum/blob/master/docs/symfony/get-it-started.md#payment-is-done
     */
    #[Route(
        path: '/payment/{id}/done',
        name: self::ROUTE_NAME,
        requirements: ['id' => Requirement::UUID_V6],
    )]
    public function __invoke(Request $request, #[MapEntity(expr: 'repository.findOneActive(id)')] PlatformOffer $platformOffer, #[CurrentUser] User $user): Response
    {
        try {
            /** @var PaymentToken $token */
            $token = $this->payum->getHttpRequestVerifier()->verify($request);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new UnprocessableEntityHttpException('Cannot verify Payum token.');
        }

        /** @var GetHumanStatus $status */
        $status = $this->commandBus->dispatch(new PlatformMembershipPaidCommand($platformOffer->getId(), $user->getId(), $token));

        $request->getSession()->remove('payment_in_progress');
        // Not captured
        if (!$status->isCaptured()) {
            $this->addFlashWarning($this->translator->trans($this->getI18nPrefix().'.status.'.$status->getValue()));

            return $this->redirectToRoute('app_user_my_account');
        }

        $this->addFlashSuccess($this->translator->trans($this->getI18nPrefix().'.flash.success', [
            '%platform%' => $platformOffer->getConfiguration()?->getPlatformName()],
        ));

        $group = $user->getMyGroupsAsInvited()->first();
        if ($group !== false) {
            return $this->redirectToRoute('app_group_show_logged', $group->getRoutingParameters());
        }

        return $this->redirectToRoute('app_user_my_account');
    }
}
