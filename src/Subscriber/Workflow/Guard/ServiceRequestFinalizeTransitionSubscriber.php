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
 * Business rule: "Only the owner of the product can trigger the "finalize" transition.".
 */
final class ServiceRequestFinalizeTransitionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public readonly Security $security
    ) {
    }

    public function guardFinalize(GuardEvent $event): void
    {
        $event->setBlocked(false);

        /** @var ServiceRequest $sr */
        $sr = $event->getSubject();

        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user !== null && !$sr->isOwner($user)) {
            $event->setBlocked(true, 'Only the owner of the object can trigger the "finalize" transition.');
        }

        $today = new \DateTimeImmutable('today');
        if ($sr->getStartAt() > $today) {
            $event->setBlocked(true, 'We must be in the transaction interval to finalize it.');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ServiceRequestStatusWorkflow::WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_FINALIZE_EVENT => ['guardFinalize'],
        ];
    }
}
