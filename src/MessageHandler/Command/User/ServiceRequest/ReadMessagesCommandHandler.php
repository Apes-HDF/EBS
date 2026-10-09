<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User\ServiceRequest;

use App\Doctrine\Manager\ServiceRequestManager;
use App\Message\Command\User\ServiceRequest\ReadMessagesCommand;
use App\Repository\ServiceRequestRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Read all messages for a given user.
 */
#[AsMessageHandler]
final readonly class ReadMessagesCommandHandler
{
    public function __construct(
        private ServiceRequestRepository $serviceRequestRepository,
        private UserRepository $userRepository,
        private ServiceRequestManager $serviceRequestManager,
    ) {
    }

    public function __invoke(ReadMessagesCommand $message): void
    {
        $serviceRequest = $this->serviceRequestRepository->get($message->requestServiceId);
        $reader = $this->userRepository->get($message->readerId);
        $this->serviceRequestManager->readMessages($serviceRequest, $reader);
    }
}
