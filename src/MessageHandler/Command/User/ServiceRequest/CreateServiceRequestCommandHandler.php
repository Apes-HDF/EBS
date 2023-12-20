<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User\ServiceRequest;

use App\Controller\i18nTrait;
use App\Doctrine\Manager\MessageManager;
use App\Doctrine\Manager\ProductAvailabilityManager;
use App\Doctrine\Manager\ServiceRequestManager;
use App\Entity\ServiceRequest;
use App\Mailer\AppMailer;
use App\Mailer\Email\ServiceRequest\NewServiceRequest;
use App\Message\Command\User\ServiceRequest\CreateServiceRequestCommand;
use App\Notifier\SmsNotifier;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Create a new service request
 * 1. create the service request
 * 2. create the messages
 * 3. send the email.
 *
 * @see ServiceRequestController
 */
#[AsMessageHandler]
final class CreateServiceRequestCommandHandler
{
    use i18nTrait;
    public const MESSAGE_SYSTEM_NEW = 'message.system.new';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ProductRepository $productRepository,
        private readonly ServiceRequestManager $serviceRequestManager,
        private readonly MessageManager $messageManager,
        private readonly ProductAvailabilityManager $productAvailabilityManager,
        private readonly AppMailer $appMailer,
        private readonly SmsNotifier $notifier,
        private readonly TranslatorInterface $translator,
        #[Autowire('%brand%')]
        private readonly string $brand,
    ) {
    }

    public function __invoke(CreateServiceRequestCommand $message): ServiceRequest
    {
        // Service request
        $product = $this->productRepository->get($message->productId);
        $recipient = $this->userRepository->get($message->recipientId);
        $serviceRequest = $product->createServiceRequest($recipient, $message->startAt, $message->endAt);
        $this->serviceRequestManager->save($serviceRequest, true);

        // Initialize the conversation
        $dateFormat = $this->translator->trans('format.date', [], 'date');
        $systemMessage = $this->messageManager->createSystemMessage($serviceRequest, self::MESSAGE_SYSTEM_NEW, [
            '%recipient%' => $recipient->getDisplayName(),
            '%startAt%' => $serviceRequest->getStartAt()->format($dateFormat),
            '%endAt%' => $serviceRequest->getEndAt()->format($dateFormat),
        ]);
        $this->messageManager->save($systemMessage, true);

        // Optional user message
        if ($message->message !== null) {
            $createdAt = $systemMessage->getCreatedAt()->modify('+1 second'); // add 1 second, so the messages order is more natural
            $userMessage = $this->messageManager->createFromRecipientMessage($serviceRequest, $message->message, $createdAt);
            $this->messageManager->save($userMessage, true);
        }

        // modifiy the product availability
        $pa = $this->productAvailabilityManager->createFromServiceRequest($serviceRequest, $message->startAt, $message->endAt);
        $this->productAvailabilityManager->save($pa, true);

        // Send email&sms
        $this->appMailer->send(NewServiceRequest::class, ['service_request' => $serviceRequest]);
        $this->sendSms($serviceRequest);

        return $serviceRequest;
    }

    private function sendSms(ServiceRequest $serviceRequest): void
    {
        $i18nPrefix = $this->getI18nPrefix(NewServiceRequest::class);
        $subject = $this->translator->trans($i18nPrefix.'.subject', ['%brand%' => $this->brand], AppMailer::TR_DOMAIN);
        $this->notifier->notify(
            $serviceRequest->getOwner(),
            $subject,
        );
    }
}
