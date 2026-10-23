<?php

declare(strict_types=1);

namespace App\Subscriber\Security;

use App\Controller\Admin\GroupAdminSecuredCrudControllerInterface;
use App\Entity\Group;
use App\Entity\GroupOffer;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Repository\UserGroupRepository;
use EasyCorp\Bundle\EasyAdminBundle\Event\AbstractLifecycleEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The group admin CRUD controllers only filter their index page: this subscriber
 * checks that the group, membership or offer targeted by any other action (detail,
 * edit, delete, batch delete, custom actions) belongs to a group administrated by
 * the current user. Platform admins are not restricted.
 *
 * @see GroupAdminCrossGroupAccessTest
 */
final class GroupAdminEntitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly AdminContextProvider $adminContextProvider,
        private readonly UserGroupRepository $userGroupRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // after the EasyAdmin router (priority 128) which resolves the CRUD controller
            KernelEvents::CONTROLLER => ['onController', -8],
            BeforeEntityUpdatedEvent::class => 'onEntityEvent',
            BeforeEntityPersistedEvent::class => 'onEntityEvent',
            BeforeEntityDeletedEvent::class => 'onEntityEvent',
        ];
    }

    public function onController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $context = $this->adminContextProvider->getContext();
        $crud = $context?->getCrud();
        $controllerFqcn = $crud?->getControllerFqcn();
        if ($crud === null || $controllerFqcn === null || !is_a($controllerFqcn, GroupAdminSecuredCrudControllerInterface::class, true)) {
            return;
        }

        $this->check($context->getEntity()->getInstance());
    }

    /**
     * @param AbstractLifecycleEvent<object> $event
     */
    public function onEntityEvent(AbstractLifecycleEvent $event): void
    {
        $this->check($event->getEntityInstance());
    }

    private function check(mixed $entity): void
    {
        $group = match (true) {
            $entity instanceof Group => $entity,
            $entity instanceof UserGroup, $entity instanceof GroupOffer => $entity->getGroup(),
            default => null,
        };
        // no entity (index, new form) or entity not related to a group
        if ($group === null || $this->security->isGranted(User::ROLE_ADMIN)) {
            return;
        }

        $user = $this->security->getUser();
        // read from the database, not from the memberships of the user which may be
        // modified by the form being submitted
        if (!$user instanceof User || !\in_array($group->getId()->toRfc4122(), $this->userGroupRepository->getAdministratedGroupIds($user), true)) {
            throw new AccessDeniedHttpException('This group is not administrated by the current user.');
        }
    }
}
