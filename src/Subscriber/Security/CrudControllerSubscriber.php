<?php

declare(strict_types=1);

namespace App\Subscriber\Security;

use App\Controller\Admin\AdminSecuredCrudControllerInterface;
use App\Controller\Admin\GroupAdminSecuredCrudControllerInterface;
use App\Security\Checker\AuthorizationChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * This subscriber checks for specific roles in the admin section.
 */
final class CrudControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationChecker $authorizationChecker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => 'onController',
        ];
    }

    public function onController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        if (\is_array($controller)) {
            $ctrl = $controller[0] ?? null;
            if (is_a($ctrl, AdminSecuredCrudControllerInterface::class)) {
                $this->authorizationChecker->checkAdminRole();
            }
            if (is_a($ctrl, GroupAdminSecuredCrudControllerInterface::class)) {
                $this->authorizationChecker->isGroupAdmin();
            }
        }
    }
}
