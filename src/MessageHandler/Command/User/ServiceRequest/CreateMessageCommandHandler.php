<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User\ServiceRequest;

use App\Doctrine\Manager\MessageManager;
use App\Mailer\AppMailer;
use App\Mailer\Email\ServiceRequest\NewMessageEmail;
use App\Message\Command\User\ServiceRequest\CreateMessageCommand;
use App\Repository\ServiceRequestRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Create a new service request
 * 1. create the service request
 * 2. create the messages
 * 3. send the email.
 *
 * @todo date format must be translated
 */
#[AsMessageHandler]
final class CreateMessageCommandHandler
{
    public function __construct(
        private readonly ServiceRequestRepository $serviceRequestRepository,
        private readonly UserRepository $userRepository,
        private readonly MessageManager $messageManager,
        private readonly AppMailer $appMailer,
    ) {
    }

    public function __invoke(CreateMessageCommand $message): void
    {
        // create the message
        $serviceRequest = $this->serviceRequestRepository->get($message->requestServiceId);
        $sender = $this->userRepository->get($message->senderId);

        if ($serviceRequest->isOwner($sender)) {
            $message = $this->messageManager->createFromOwnerMessage($serviceRequest, $message->message);
        } else {
            $message = $this->messageManager->createFromRecipientMessage($serviceRequest, $message->message);
        }
        $this->messageManager->save($message, true);

        // Send email
        $this->appMailer->send(NewMessageEmail::class, ['service_request' => $serviceRequest, 'message' => $message]);
    }
}
