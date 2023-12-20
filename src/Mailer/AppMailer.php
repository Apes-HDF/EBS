<?php

declare(strict_types=1);

namespace App\Mailer;

use App\Entity\Configuration;
use App\Mailer\Email\EmailInterface;
use App\Repository\ConfigurationRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Webmozart\Assert\Assert;

/**
 * Centralize all emails proccessing.
 */
final class AppMailer
{
    // translation domain
    public const TR_DOMAIN = 'email';

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EmailCollection $emailCollection,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    /**
     * @param class-string         $emailCode
     * @param array<string, mixed> $context
     *
     * @throws TransportExceptionInterface
     */
    public function send(string $emailCode, array $context): void
    {
        $email = null;
        foreach ($this->emailCollection->getEmails() as $appEmail) {
            /** @var EmailInterface $appEmail */
            if ($appEmail->supports($emailCode)) {
                $email = $appEmail->getEmail($context);
                break;
            }
        }

        if ($email === null) {
            throw new \LogicException("No email found to process the $emailCode email");
        }

        $configuration = $this->configurationRepository->getInstanceConfiguration();
        Assert::isInstanceOf($configuration, Configuration::class);
        $from = new Address($configuration->getNotificationsSenderEmail(), $configuration->getNotificationsSenderName());
        $email->from($from);

        $this->mailer->send($email);
    }
}
