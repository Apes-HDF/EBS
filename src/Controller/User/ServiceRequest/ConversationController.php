<?php

declare(strict_types=1);

namespace App\Controller\User\ServiceRequest;

use App\Controller\FlashTrait;
use App\Controller\i18nTrait;
use App\Controller\SecurityTrait;
use App\Controller\User\MyAccountAction;
use App\Entity\Message;
use App\Entity\User;
use App\Form\Type\ServiceRequest\ModifyServiceRequestType;
use App\Form\Type\User\ServiceRequest\NewMessageType;
use App\Message\Command\User\ServiceRequest\CreateMessageCommand;
use App\Message\Command\User\ServiceRequest\ReadMessagesCommand;
use App\Message\Command\User\ServiceRequest\TryAutoFinalizeCommand;
use App\MessageBus\CommandBus;
use App\MessageBus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see ConversationControllerTest
 */
#[isGranted(User::ROLE_USER)]
class ConversationController extends AbstractController
{
    use FlashTrait;
    use SecurityTrait;
    use ServiceRequestTrait;
    use i18nTrait;

    final public const ROUTE = 'app_user_conversation_list';

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route(path: [
        'en' => MyAccountAction::BASE_URL_EN.'/service/{id}/conversation',
        'fr' => MyAccountAction::BASE_URL_FR.'/service/{id}/conversation',
    ], name: self::ROUTE, requirements: ['id' => Requirement::UUID_V6])]
    public function __invoke(Request $request, string $id): Response
    {
        $serviceRequest = $this->getMyServiceRequest($id);
        $this->commandBus->dispatch(new TryAutoFinalizeCommand($serviceRequest->getId()));
        $this->commandBus->dispatch(new ReadMessagesCommand($serviceRequest->getId(), $this->getAppUser()->getId()));

        // we need to refresh the entity in case it was modified by the commands
        $serviceRequest = $this->getMyServiceRequest($id);

        // form to add a new message
        $form = $this->createForm(NewMessageType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Message $message */
            $message = $form->getData();
            $this->commandBus->dispatch(new CreateMessageCommand($serviceRequest->getId(), $this->getAppUser()->getId(), $message->getMessage()));
            $this->addFlashSuccess($this->getI18nPrefix().'.flash.success');

            return $this->redirectToRoute(self::ROUTE, ['id' => $id]);
        }

        // form to modify the dates of the service request
        $modifyForm = $this->createForm(ModifyServiceRequestType::class, $serviceRequest)->handleRequest($request);

        return $this->render('pages/account/conversation.html.twig', [
            'service_request' => $serviceRequest,
            'form' => $form,
            'modify_form' => $modifyForm,
        ]);
    }
}
