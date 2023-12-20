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
 * Business rule: only the owner of the product can trigger the "modifyOwner" transition.
 */
final class ServiceRequestModifyOwnerTransitionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        public readonly Security $security
    ) {
    }

    public function guardModifyOwner(GuardEvent $event): void
    {
        $event->setBlocked(false);

        /** @var ServiceRequest $sr */
        $sr = $event->getSubject();

        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user !== null && !$sr->isOwner($user)) {
            $event->setBlocked(true, 'Only the owner of the object can trigger the "modifyOwner" transition.');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ServiceRequestStatusWorkflow::WORKFLOW_SERVICE_REQUEST_STATUS_GUARD_MODIFY_OWNER_EVENT => ['guardModifyOwner'],
        ];
    }
}
