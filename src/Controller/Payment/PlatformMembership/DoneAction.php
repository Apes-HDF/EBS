<?php

declare(strict_types=1);

namespace App\Controller\Payment\PlatformMembership;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Doctrine\Manager\UserManager;
use App\Entity\PaymentToken;
use App\Entity\PlatformOffer;
use App\Entity\User;
use Carbon\CarbonImmutable;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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

    public const ROUTE_NAME = 'app_platform_payment_done';

    /**
     * @see https://github.com/Payum/Payum/blob/master/docs/symfony/get-it-started.md#payment-is-done
     */
    #[Route(
        path: '/payment/{id}/done',
        name: self::ROUTE_NAME,
        requirements: ['id' => Requirement::UUID_V6],
    )]
    public function __invoke(Request $request, #[MapEntity(expr: 'repository.findOneActive(id)')] PlatformOffer $platformOffer, #[CurrentUser] User $user, Payum $payum, TranslatorInterface $translator, UserManager $userManager, LoggerInterface $logger): Response
    {
        try {
            /** @var PaymentToken $token */
            $token = $payum->getHttpRequestVerifier()->verify($request);
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
            throw new UnprocessableEntityHttpException('Cannot verify Payum token.');
        }

        $gateway = $payum->getGateway($token->getGatewayName());
        $status = new GetHumanStatus($token);
        $gateway->execute($status);

        // Not captured
        if (!$status->isCaptured()) {
            $this->addFlashWarning($translator->trans($this->getI18nPrefix().'.status.'.$status->getValue()));
        }

        $user
            ->setMembershipPaid(true)
            ->setStartAt(CarbonImmutable::today())
            ->setPayedAt(CarbonImmutable::now())
        ;
        if (($offerType = $platformOffer->getType())->isRecurring()) {
            $user->setEndAt(new CarbonImmutable($offerType->getEndAtInterval()));
        }

        $userManager->save($user, true);

        $this->addFlashSuccess($translator->trans($this->getI18nPrefix().'.flash.success', [
            '%platform%' => $platformOffer->getConfiguration()?->getPlatformName()],
        ));
        $request->getSession()->remove('payment_in_progress');

        return $this->redirectToRoute('app_user_my_account');
    }
}
