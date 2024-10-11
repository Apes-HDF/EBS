<?php

declare(strict_types=1);

namespace App\Controller\Payment\PlatformMembership;

use App\Entity\PlatformOffer;
use App\Entity\User;
use App\Payment\PayumManager;
use App\Repository\ConfigurationRepository;
use App\Repository\PlatformOfferRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_USER)]
final class PrepareAction extends AbstractController
{
    public const ROUTE_NAME = 'app_platform_payment_prepare';

    /**
     * @see https://github.com/Payum/Payum/blob/master/docs/symfony/get-it-started.md#prepare-order)
     */
    #[Route(
        path: '/payment/{id}/prepare',
        name: self::ROUTE_NAME,
        requirements: ['id' => Requirement::UUID_V6],
        methods: ['POST'],
    )]
    public function preparePayment(Request $request, #[MapEntity(expr: 'repository.findOneActive(id)')] PlatformOffer $platformOffer, #[CurrentUser] User $user, PayumManager $payumManager): Response
    {
        /** @var ?string $token */
        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('payment_prepare', $token)) {
            throw new UnprocessableEntityHttpException('Invalid CSRF token');
        }

        $request->getSession()->set('payment_in_progress', true);

        // create and save the payment main reference
        $payment = $payumManager->getPayment($platformOffer, $user);

        // create the capture token and redirect to the capture action
        $captureToken = $payumManager->getCaptureToken($payment, DoneAction::ROUTE_NAME, [
            'id' => (string) $platformOffer->getId(),
        ]);

        return $this->redirect($captureToken->getTargetUrl());
    }

    #[Route(path: [
        'en' => '/en/subcription',
        'fr' => '/fr/abonnement',
    ], name: 'redirect_to_payment')]
    public function redirectToPayment(PlatformOfferRepository $platformOfferRepository, ConfigurationRepository $configurationRepository): Response
    {
        $offers = $platformOfferRepository->findBy(['active' => true]);
        $lowOffer = $platformOfferRepository->findLowOffer();
        $platformName = $configurationRepository->getInstanceConfigurationOrCreate()->getPlatformName();

        return $this->render('pages/redirect_to_payment.html.twig', compact('offers', 'lowOffer', 'platformName'));
    }
}
