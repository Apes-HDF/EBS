<?php

declare(strict_types=1);

namespace App\Doctrine\Listener;

use App\Entity\User;
use App\Repository\ConfigurationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener(event: ExceptionEvent::class, method: 'onKernelException')]
final readonly class MembershipPaidListener
{
    public function __construct(
        private ConfigurationRepository $configurationRepository,
        private Security $security,
        private RouterInterface $router,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }
        $config = $this->configurationRepository->getInstanceConfigurationOrCreate();
        $session = $event->getRequest()->getSession();
        /** @var bool $isPaymentInProgress */
        $isPaymentInProgress = $session->get('payment_in_progress');

        if ($config->getPaidMembership() && !$user->isMembershipPaid() && !$isPaymentInProgress) {
            $event->setResponse(new RedirectResponse($this->router->generate('redirect_to_payment')));
        }
    }
}
