<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User\ServiceRequest;

use App\Doctrine\Manager\ServiceRequestManager;
use App\Message\Command\User\ServiceRequest\TryAutoFinalizeCommand;
use App\Repository\ServiceRequestRepository;
use App\Workflow\ServiceRequestStatusWorkflow;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Try to auto-finalize a service request when we come back on the detail of a
 * request and it's end date has been passed. It's a system transition.
 *
 * @see ConversationController
 */
#[AsMessageHandler]
final class TryAutoFinalizeCommandHandler
{
    public function __construct(
        private readonly ServiceRequestRepository $serviceRequestRepository,
        private readonly ServiceRequestManager $serviceRequestManager,
        private readonly ServiceRequestStatusWorkflow $serviceRequestStatusWorkflow,
    ) {
    }

    public function __invoke(TryAutoFinalizeCommand $message): void
    {
        $serviceRequest = $this->serviceRequestRepository->get($message->requestServiceId);
        if ($this->serviceRequestStatusWorkflow->canAutoFinalize($serviceRequest)) {
            $this->serviceRequestStatusWorkflow->autoFinalize($serviceRequest);
            $this->serviceRequestManager->save($serviceRequest, true);
        }
    }
}
