<?php

declare(strict_types=1);

namespace App\Message\Command\Payment;

use App\Entity\PaymentToken;
use Symfony\Component\Uid\Uuid;

/**
 * @see DoneCommandHandler
 */
final readonly class DoneCommand
{
    public function __construct(
        public Uuid $groupOfferId,
        public Uuid $userId,
        public PaymentToken $paymentToken,
    ) {
    }
}
