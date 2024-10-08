<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Entity\ServiceRequest;
use App\Enum\ServiceRequest\ServiceRequestStatusTransition;
use Symfony\Component\Workflow\WorkflowInterface;

final class ServiceRequestStatusWorkflow
{
    private const EXCEPTION_MESSAGE = 'Cannot apply the "%s" transition on service request n°%s, current status: "%s"';

    // Guards
    public const WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_ACCEPT_EVENT = 'workflow.service_request_status.guard.accept'; // @PHP 8.2, use enum value.
    public const WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_MODIFY_OWNER_EVENT = 'workflow.service_request_status.guard.modifyOwner';
    public const WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_MODIFY_RECIPIENT_EVENT = 'workflow.service_request_status.guard.modifyRecipient';
    public const WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_CONFIRM_EVENT = 'workflow.service_request_status.guard.confirm';
    public const WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_FINALIZE_EVENT = 'workflow.service_request_status.guard.finalize';
    public const WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_AUTO_FINALIZE_EVENT = 'workflow.service_request_status.guard.autoFinalize';

    public const WORKFLOW_SERVICE_REQUEST_COMPLETED_ACCEPT_EVENT = 'workflow.service_request_status.completed.accept';
    public const WORKFLOW_SERVICE_REQUEST_COMPLETED_MODIFY_OWNER_EVENT = 'workflow.service_request_status.completed.modifyOwner';
    public const WORKFLOW_SERVICE_REQUEST_COMPLETED_MODIFY_RECIPIENT_EVENT = 'workflow.service_request_status.completed.modifyRecipient';
    public const WORKFLOW_SERVICE_REQUEST_COMPLETED_CONFIRM_EVENT = 'workflow.service_request_status.completed.confirm';

    public const WORKFLOW_SERVICE_REQUEST_COMPLETED_REFUSE_EVENT = 'workflow.service_request_status.completed.refuse';

    public const WORKFLOW_SERVICE_REQUEST_COMPLETED_FINALIZE_EVENT = 'workflow.service_request_status.completed.finalize';
    public const WORKFLOW_SERVICE_REQUEST_COMPLETED_AUTO_FINALIZE_EVENT = 'workflow.service_request_status.completed.autoFinalize';

    public function __construct(
        private readonly WorkflowInterface $serviceRequestStatusStateMachine,
    ) {
    }

    /**
     * Apply a given transition (enum) to a service request (manual transitions).
     *
     * @throws \LogicException
     */
    public function apply(ServiceRequest $sr, ServiceRequestStatusTransition $serviceRequestStatusTransition): void
    {
        switch ($serviceRequestStatusTransition) {
            case ServiceRequestStatusTransition::ACCEPT:
                $this->accept($sr);
                break;
            case ServiceRequestStatusTransition::CONFIRM:
                $this->confirm($sr);
                break;
            case ServiceRequestStatusTransition::MODIFY_OWNER:
                $this->modifyOwner($sr);
                break;
            case ServiceRequestStatusTransition::MODIFY_RECIPIENT:
                $this->modifyRecipient($sr);
                break;
            case ServiceRequestStatusTransition::REFUSE:
                $this->refuse($sr);
                break;
            case ServiceRequestStatusTransition::FINALIZE:
                $this->finalize($sr);
                break;
        }
    }

    private function getException(ServiceRequest $sr, ServiceRequestStatusTransition $transition): \LogicException
    {
        return new \LogicException(\sprintf(self::EXCEPTION_MESSAGE, $transition->name, $sr->getId(), $sr->getStatus()->value));
    }

    public function canAccept(ServiceRequest $sr): bool
    {
        return $this->serviceRequestStatusStateMachine->can($sr, ServiceRequestStatusTransition::ACCEPT->value);
    }

    public function accept(ServiceRequest $sr): ServiceRequest
    {
        if (!$this->canAccept($sr)) {
            throw $this->getException($sr, ServiceRequestStatusTransition::ACCEPT);
        }

        $this->serviceRequestStatusStateMachine->apply($sr, ServiceRequestStatusTransition::ACCEPT->value);

        return $sr;
    }

    public function canModifyOwner(ServiceRequest $sr): bool
    {
        return $this->serviceRequestStatusStateMachine->can($sr, ServiceRequestStatusTransition::MODIFY_OWNER->value);
    }

    public function modifyOwner(ServiceRequest $sr): ServiceRequest
    {
        if (!$this->canModifyOwner($sr)) {
            throw $this->getException($sr, ServiceRequestStatusTransition::MODIFY_OWNER);
        }

        $this->serviceRequestStatusStateMachine->apply($sr, ServiceRequestStatusTransition::MODIFY_OWNER->value);

        return $sr;
    }

    public function canModifyRecipient(ServiceRequest $sr): bool
    {
        return $this->serviceRequestStatusStateMachine->can($sr, ServiceRequestStatusTransition::MODIFY_RECIPIENT->value);
    }

    public function modifyRecipient(ServiceRequest $sr): ServiceRequest
    {
        if (!$this->canModifyRecipient($sr)) {
            throw $this->getException($sr, ServiceRequestStatusTransition::MODIFY_RECIPIENT);
        }

        $this->serviceRequestStatusStateMachine->apply($sr, ServiceRequestStatusTransition::MODIFY_RECIPIENT->value);

        return $sr;
    }

    public function canConfirm(ServiceRequest $sr): bool
    {
        return $this->serviceRequestStatusStateMachine->can($sr, ServiceRequestStatusTransition::CONFIRM->value);
    }

    public function confirm(ServiceRequest $sr): ServiceRequest
    {
        if (!$this->canConfirm($sr)) {
            throw $this->getException($sr, ServiceRequestStatusTransition::CONFIRM);
        }

        $this->serviceRequestStatusStateMachine->apply($sr, ServiceRequestStatusTransition::CONFIRM->value);

        return $sr;
    }

    public function canRefuse(ServiceRequest $sr): bool
    {
        return $this->serviceRequestStatusStateMachine->can($sr, ServiceRequestStatusTransition::REFUSE->value);
    }

    public function refuse(ServiceRequest $sr): ServiceRequest
    {
        if (!$this->canRefuse($sr)) {
            throw $this->getException($sr, ServiceRequestStatusTransition::REFUSE);
        }

        $this->serviceRequestStatusStateMachine->apply($sr, ServiceRequestStatusTransition::REFUSE->value);

        return $sr;
    }

    public function canFinalize(ServiceRequest $sr): bool
    {
        return $this->serviceRequestStatusStateMachine->can($sr, ServiceRequestStatusTransition::FINALIZE->value);
    }

    public function finalize(ServiceRequest $sr): ServiceRequest
    {
        if (!$this->canFinalize($sr)) {
            throw $this->getException($sr, ServiceRequestStatusTransition::FINALIZE);
        }

        $this->serviceRequestStatusStateMachine->apply($sr, ServiceRequestStatusTransition::FINALIZE->value);

        return $sr;
    }

    public function canAutoFinalize(ServiceRequest $sr): bool
    {
        return $this->serviceRequestStatusStateMachine->can($sr, ServiceRequestStatusTransition::AUTO_FINALIZE->value);
    }

    public function autoFinalize(ServiceRequest $sr): ServiceRequest
    {
        if (!$this->canAutoFinalize($sr)) {
            throw $this->getException($sr, ServiceRequestStatusTransition::AUTO_FINALIZE);
        }

        $this->serviceRequestStatusStateMachine->apply($sr, ServiceRequestStatusTransition::AUTO_FINALIZE->value);

        return $sr;
    }
}
