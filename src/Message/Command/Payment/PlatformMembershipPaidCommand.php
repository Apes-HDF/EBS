<?php

declare(strict_types=1);

namespace App\Message\Command\Payment;

use App\Entity\PaymentToken;
use App\MessageHandler\Command\Payment\PlatformMembershipPaidCommandHandler;
use Symfony\Component\Uid\Uuid;

/**
 * @see PlatformMembershipPaidCommandHandler
 */
final readonly class PlatformMembershipPaidCommand
{
    public function __construct(
        public Uuid $platformOfferId,
        public Uuid $userId,
        public PaymentToken $paymentToken,
    ) {
    }
}
