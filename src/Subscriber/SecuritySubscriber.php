<?php

declare(strict_types=1);

namespace App\Subscriber;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class SecuritySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        // redirect admins to the admin space
        if ($user->isAdmin()) {
            $event->setResponse(new RedirectResponse($this->router->generate('admin', [], UrlGeneratorInterface::ABSOLUTE_URL)));
        }

        $this->userManager->updateLoginAt($user);
    }
}
