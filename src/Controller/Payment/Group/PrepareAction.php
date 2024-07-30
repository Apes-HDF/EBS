<?php

declare(strict_types=1);

namespace App\Controller\Payment\Group;

use App\Controller\User\MyAccountAction;
use App\Entity\User;
use App\Payment\PayumManager;
use App\Repository\GroupOfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see PrepareActionTest
 */
#[IsGranted(User::ROLE_USER)]
final class PrepareAction extends AbstractController
{
    use GroupOfferTrait;

    public const ROUTE_NAME = 'app_payment_prepare';

    public function __construct(
        private readonly GroupOfferRepository $groupOfferRepository,
        private readonly PayumManager $payumManager,
    ) {
    }

    /**
     * @see https://github.com/Payum/Payum/blob/master/docs/symfony/get-it-started.md#prepare-order)
     */
    #[Route(
        path: MyAccountAction::BASE_URL_EN.'/payment/{id}/prepare',
        name: self::ROUTE_NAME,
        requirements: ['id' => Requirement::UUID_V6],
        methods: ['POST'],
    )]
    public function __invoke(Request $request, string $id, #[CurrentUser] User $user): Response
    {
        $groupOffer = $this->getGroupOffer($id);

        /** @var ?string $token */
        $token = $request->request->get('token');
        if (!$this->isCsrfTokenValid('payment_prepare', $token)) {
            throw new UnprocessableEntityHttpException('Invalid CSRF token');
        }

        // create and save the payment main reference
        $payment = $this->payumManager->getPayment($groupOffer, $user);

        // create the capture token and redirect to the capture action
        $captureToken = $this->payumManager->getCaptureToken($payment, DoneAction::ROUTE_NAME, [
            'id' => $id,
        ]);

        return $this->redirect($captureToken->getTargetUrl());
    }
}
