<?php

declare(strict_types=1);

namespace App\Mailer\Email\ServiceRequest;

use App\Controller\i18nTrait;
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
 * Email that is sent to the recipient to notify that the request was accepted by
 * the owner.
 *
 * @see ServiceRequestAcceptedSubscriber
 */
final class ServiceRequestAccepted implements EmailInterface
{
    use i18nTrait;
    use EmailTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        #[Autowire('%brand%')]
        private readonly string $brand,
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

        return (new TemplatedEmail())
            ->to($serviceRequest->getRecipient()->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($this->translator->trans($this->getI18nPrefix().'.subject', ['%brand%' => $this->brand], AppMailer::TR_DOMAIN))
            ->htmlTemplate('email/service_request/accepted.html.twig')
            ->context($context);
    }
}
