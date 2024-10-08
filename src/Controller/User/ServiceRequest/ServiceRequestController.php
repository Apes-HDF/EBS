<?php

declare(strict_types=1);

namespace App\Controller\User\ServiceRequest;

use App\Controller\FlashTrait;
use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Doctrine\Manager\ServiceRequestManager;
use App\Entity\Product;
use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Form\Type\ServiceRequest\CreateServiceRequestType;
use App\Message\Command\User\ServiceRequest\CreateServiceRequestCommand;
use App\Message\Query\Product\GetProductByIdQuery;
use App\MessageBus\CommandBus;
use App\MessageBus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

/**
 * @see ServiceRequestControllerTest
 */
#[IsGranted(User::ROLE_USER)]
class ServiceRequestController extends AbstractController
{
    use FlashTrait;
    use SecurityTrait;

    final public const ROUTE = 'app_user_service_request_new';

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
        private readonly ServiceRequestManager $serviceRequestManager,
    ) {
    }

    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/new-service-request/{id}',
        'fr' => MyAccountAction::BASE_URL_FR.'/nouvelle-demande-de-service/{id}',
    ], name: self::ROUTE, requirements: ['id' => Requirement::UUID_V6])]
    public function __invoke(Request $request, string $id, #[CurrentUser] User $user): Response
    {
        try {
            /** @var Product $product */
            $product = $this->queryBus->query(new GetProductByIdQuery(Uuid::fromString($id)));
        } catch (HandlerFailedException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $serviceRequest = $this->serviceRequestManager->initFormProductAndRequest($product, $request);
        $form = $this->createForm(CreateServiceRequestType::class, $serviceRequest)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ServiceRequest $newSr */
            $newSr = $form->getData();
            $command = new CreateServiceRequestCommand($product->getId(), $user->getId(), $newSr->getStartAt(), $newSr->getEndAt(), $newSr->getMessage());
            /** @var ServiceRequest $serviceRequest */
            $serviceRequest = $this->commandBus->dispatch($command);
            $this->addFlashSuccess('loan.new_action.form.success');

            return $this->redirectToRoute(ConversationController::ROUTE, ['id' => (string) $serviceRequest->getId()]);
        }

        return $this->render('pages/account/loans/new.html.twig', compact('form', 'product'));
    }
}
