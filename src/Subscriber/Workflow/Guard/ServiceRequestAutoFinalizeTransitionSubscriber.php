<?php

declare(strict_types=1);

namespace App\Subscriber\Workflow\Guard;

use App\Entity\ServiceRequest;
use App\Workflow\ServiceRequestStatusWorkflow;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Business rule: the autoFinalize transition can only be done if the endAt is passed.
 */
final class ServiceRequestAutoFinalizeTransitionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public readonly Security $security,
    ) {
    }

    public function guardAutoFinalize(GuardEvent $event): void
    {
        $event->setBlocked(false);

        /** @var ServiceRequest $sr */
        $sr = $event->getSubject();
        $today = new \DateTimeImmutable('today');
        if ($today <= $sr->getEndAt()) {
            $event->setBlocked(true, 'the autoFinalize is blocked if the endAt is before or today.');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ServiceRequestStatusWorkflow::WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_AUTO_FINALIZE_EVENT => ['guardAutoFinalize'],
        ];
    }
}
