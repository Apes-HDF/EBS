<?php

declare(strict_types=1);

namespace App\Subscriber\Workflow;

use App\Doctrine\Manager\MessageManager;
use App\Entity\ServiceRequest;
use App\Mailer\AppMailer;
use App\Mailer\Email\ServiceRequest\ServiceRequestConfirmed;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Workflow\ServiceRequestStatusWorkflow;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ServiceRequestConfirmedSubscriber implements EventSubscriberInterface
{
    use SmsNotifierTrait;
    public const MESSAGE_SYSTEM_CONFIRMED = 'message.system.confirmed';

    public function __construct(
        private readonly MessageManager $messageManager,
        private readonly AppMailer $appMailer,
        private readonly SmsNotifier $notifier,
        private readonly TranslatorInterface $translator,
        #[Autowire('%brand%')]
        private readonly string $brand,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ServiceRequestStatusWorkflow::WORKFLOW_SERVICE_REQUEST_COMPLETED_CONFIRM_EVENT => 'onCompleted',
        ];
    }

    public function onCompleted(Event $event): void
    {
        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $event->getSubject();
        $this->createSystemMessage($serviceRequest);
        $this->appMailer->send(ServiceRequestConfirmed::class, ['service_request' => $serviceRequest]);
        $this->sendSms($serviceRequest->getOwner(), ServiceRequestConfirmed::class);
    }

    private function createSystemMessage(ServiceRequest $serviceRequest): void
    {
        $product = $serviceRequest->getProduct();
        $systemMessage = $this->messageManager->createSystemMessage(
            $serviceRequest,
            self::MESSAGE_SYSTEM_CONFIRMED.'.'.$product->getType()->value,
        );
        $this->messageManager->save($systemMessage, true);
    }
}
