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
        // [object, method] for CRUD controllers, object for invokable controllers
        $ctrl = \is_array($controller) ? ($controller[0] ?? null) : $controller;
        if (!\is_object($ctrl)) {
            return;
        }

        if ($ctrl instanceof AdminSecuredCrudControllerInterface) {
            $this->authorizationChecker->checkAdminRole();
        }
        if ($ctrl instanceof GroupAdminSecuredCrudControllerInterface) {
            $this->authorizationChecker->isGroupAdmin();
        }
    }
}
