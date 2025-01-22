<?php

declare(strict_types=1);

namespace App\Notifier;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Webmozart\Assert\Assert;

use function Symfony\Component\String\u;

/**
 * Centralize all SMS proccessing.
 */
final class SmsNotifier
{
    public function __construct(
        private readonly TexterInterface $texter,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function notify(User $user, string $subject): ?SentMessage
    {
        $this->logger->info('SMS Notification');
        if (!$user->canBeNotifiedBySms()) {
            $this->logger->warning('User cannot be notified by SMS');

            return null;
        }

        $phoneNumber = $user->getPhoneNumber();
        Assert::notEmpty($phoneNumber);
        Assert::notEmpty($subject);

        // fail silently, it should not happen as the number is validated in the form.
        // We want to avoid a 500 error from the vendors
        if (!u($phoneNumber)->startsWith('+')) {
            $this->logger->warning('Invalid phone number: '.$phoneNumber);

            return null;
        }

        try {
            $response = $this->texter->send(new SmsMessage(
                phone: $phoneNumber,
                subject: $subject
            ));
            $this->logger->info('SMS Sent Successfully');

            return $response;
        } catch (\Exception $e) {
            // OK, the sms cannot be delivered, but this is not critical as the an
            // email is always sent
            $this->logger->warning('Cannot deliver text message: '.$e->getMessage());

            return null;
        }
    }
}
