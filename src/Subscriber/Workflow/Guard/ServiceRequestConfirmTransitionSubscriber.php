<?php

declare(strict_types=1);

namespace App\Subscriber\Workflow\Guard;

use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Workflow\ServiceRequestStatusWorkflow;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Business rule: "only the recipient of the product can trigger the "confirm" transition.".
 */
final class ServiceRequestConfirmTransitionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public readonly Security $security,
    ) {
    }

    public function guardConfirm(GuardEvent $event): void
    {
        $event->setBlocked(false);

        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $event->getSubject();

        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user !== null && !$serviceRequest->isRecipient($user)) {
            $event->setBlocked(true, 'Only the recipient of the object can trigger the "confirm" transition.');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ServiceRequestStatusWorkflow::WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_CONFIRM_EVENT => ['guardConfirm'],
        ];
    }
}
