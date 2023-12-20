<?php

declare(strict_types=1);

namespace App\Subscriber\Workflow;

use App\Doctrine\Manager\MessageManager;
use App\Doctrine\Manager\ServiceRequestManager;
use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Mailer\AppMailer;
use App\Mailer\Email\ServiceRequest\ServiceRequestRefused;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Workflow\ServiceRequestStatusWorkflow;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ServiceRequestRefusedSubscriber implements EventSubscriberInterface
{
    use SmsNotifierTrait;
    final public const MESSAGE_SYSTEM_REFUSED = 'message.system.refused';

    public function __construct(
        private readonly MessageManager $messageManager,
        private readonly AppMailer $appMailer,
        private readonly Security $security,
        private readonly ServiceRequestManager $serviceRequestManager,
        private readonly TranslatorInterface $translator,
        private readonly SmsNotifier $notifier,
        #[Autowire('%brand%')]
        private readonly string $brand,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ServiceRequestStatusWorkflow::WORKFLOW_SERVICE_REQUEST_COMPLETED_REFUSE_EVENT => 'onCompleted',
        ];
    }

    public function onCompleted(Event $event): void
    {
        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $event->getSubject();
        /** @var ?User $actor */
        $actor = $this->security->getUser();
        $this->createSystemMessage($serviceRequest, $actor);
        $this->sendEmail($serviceRequest, $actor);
        $this->sendSms($serviceRequest->getOtherUser($actor), ServiceRequestRefused::class);
        $this->serviceRequestManager->deleteUnavailabilities($serviceRequest);
    }

    private function createSystemMessage(ServiceRequest $serviceRequest, ?User $actor): void
    {
        $product = $serviceRequest->getProduct();
        $systemMessage = $this->messageManager->createSystemMessage(
            $serviceRequest,
            self::MESSAGE_SYSTEM_REFUSED.'.'.$product->getType()->value,
            ['%actor%' => $actor?->getDisplayName() ?? ''],
        );
        $this->messageManager->save($systemMessage, true);
    }

    private function sendEmail(ServiceRequest $serviceRequest, ?User $actor): void
    {
        $this->appMailer->send(ServiceRequestRefused::class, [
            'service_request' => $serviceRequest,
            'actor' => $actor,
        ]);
    }
}
