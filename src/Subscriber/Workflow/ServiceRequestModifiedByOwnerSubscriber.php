<?php

declare(strict_types=1);

namespace App\Subscriber\Workflow;

use App\Controller\i18nTrait;
use App\Doctrine\Manager\MessageManager;
use App\Entity\ServiceRequest;
use App\Mailer\AppMailer;
use App\Mailer\Email\ServiceRequest\ServiceRequestModifiedByOwner;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Workflow\ServiceRequestStatusWorkflow;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ServiceRequestModifiedByOwnerSubscriber implements EventSubscriberInterface
{
    use i18nTrait;
    use SmsNotifierTrait;

    public const MESSAGE_SYSTEM_MODIFIED_BY_OWNER = 'message.system.modified_by_owner';

    public function __construct(
        private readonly MessageManager $messageManager,
        private readonly AppMailer $appMailer,
        private readonly TranslatorInterface $translator,
        private readonly SmsNotifier $notifier,
        #[Autowire('%brand%')]
        private readonly string $brand,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ServiceRequestStatusWorkflow::WORKFLOW_SERVICE_REQUEST_COMPLETED_MODIFY_OWNER_EVENT => 'onCompleted',
        ];
    }

    public function onCompleted(Event $event): void
    {
        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $event->getSubject();
        $this->createSystemMessage($serviceRequest);
        $this->appMailer->send(ServiceRequestModifiedByOwner::class, ['service_request' => $serviceRequest]);
        $this->sendSms($serviceRequest->getRecipient(), ServiceRequestModifiedByOwner::class);
    }

    private function createSystemMessage(ServiceRequest $serviceRequest): void
    {
        $product = $serviceRequest->getProduct();
        $dateFormat = $this->translator->trans('format.date', [], 'date');
        $systemMessage = $this->messageManager->createSystemMessage(
            $serviceRequest,
            self::MESSAGE_SYSTEM_MODIFIED_BY_OWNER.'.'.$product->getType()->value,
            [
                '%startAt%' => $serviceRequest->getStartAt()->format($dateFormat),
                '%endAt%' => $serviceRequest->getEndAt()->format($dateFormat),
            ]
        );
        $this->messageManager->save($systemMessage, true);
    }
}
