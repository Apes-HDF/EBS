<?php

declare(strict_types=1);

namespace App\Message\Command\Payment;

use App\Entity\PaymentToken;
use Symfony\Component\Uid\Uuid;

/**
 * @see DoneCommandHandler
 */
final class DoneCommand
{
    public function __construct(
        public readonly Uuid $groupOfferId,
        public readonly Uuid $userId,
        public readonly PaymentToken $paymentToken,
    ) {
    }
}
