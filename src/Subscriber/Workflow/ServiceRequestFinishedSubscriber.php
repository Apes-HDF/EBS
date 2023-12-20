<?php

declare(strict_types=1);

namespace App\Subscriber\Workflow;

use App\Doctrine\Manager\MessageManager;
use App\Doctrine\Manager\ServiceRequestManager;
use App\Entity\ServiceRequest;
use App\Workflow\ServiceRequestStatusWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

final class ServiceRequestFinishedSubscriber implements EventSubscriberInterface
{
    final public const MESSAGE_SYSTEM_FINALIZED = 'message.system.finalized';

    public function __construct(
        private readonly MessageManager $messageManager,
        private readonly ServiceRequestManager $serviceRequestManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ServiceRequestStatusWorkflow::WORKFLOW_SERVICE_REQUEST_COMPLETED_FINALIZE_EVENT => 'onCompleted',
            ServiceRequestStatusWorkflow::WORKFLOW_SERVICE_REQUEST_COMPLETED_AUTO_FINALIZE_EVENT => 'onCompletedAuto',
        ];
    }

    /**
     * For a manual finalization, we set the end to the current date.
     */
    public function onCompleted(Event $event): void
    {
        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $event->getSubject();
        $serviceRequest->setEndAt(new \DateTimeImmutable('today'));
        $this->serviceRequestManager->deleteUnavailabilities($serviceRequest);
        $this->serviceRequestManager->save($serviceRequest, true);
        $this->createSystemMessage($serviceRequest);
    }

    /**
     * For an auto finalize, the end date doesn't change, but we set the auto message
     * date to the day after the end date.
     */
    public function onCompletedAuto(Event $event): void
    {
        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $event->getSubject();
        $this->serviceRequestManager->deleteUnavailabilities($serviceRequest);
        $this->createSystemMessage($serviceRequest, $serviceRequest->getFinalizedAt());
    }

    private function createSystemMessage(ServiceRequest $serviceRequest, \DateTimeImmutable $createdAt = null): void
    {
        $systemMessage = $this->messageManager->createSystemMessage(
            $serviceRequest,
            self::MESSAGE_SYSTEM_FINALIZED,
            createdAt: $createdAt
        );
        $this->messageManager->save($systemMessage, true);
    }
}
