<?php

declare(strict_types=1);

namespace App\Mailer\Email\ServiceRequest;

use App\Entity\Message;
use App\Entity\ServiceRequest;
use App\Mailer\AppMailer;
use App\Mailer\Email\EmailInterface;
use App\Mailer\Email\EmailTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * New message sent for a given service conversation.
 */
final class NewMessageEmail implements EmailInterface
{
    use EmailTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        #[Autowire('%brand%')]
        private readonly string $brand
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function getEmail(array $context): TemplatedEmail
    {
        /** @var ?ServiceRequest $serviceRequest */
        $serviceRequest = $context['service_request'] ?? null;
        Assert::isInstanceOf($serviceRequest, ServiceRequest::class);

        /** @var ?Message $message */
        $message = $context['message'] ?? null;
        Assert::isInstanceOf($message, Message::class);

        return (new TemplatedEmail())
            ->to($message->getRecipient()->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($this->translator->trans('new_message.subject', ['%brand%' => $this->brand], AppMailer::TR_DOMAIN))
            ->htmlTemplate('email/service_request/message/new.html.twig')
            ->context($context)
        ;
    }
}
