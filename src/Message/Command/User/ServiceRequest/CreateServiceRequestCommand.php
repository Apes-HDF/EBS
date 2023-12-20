<?php

declare(strict_types=1);

namespace App\Message\Command\User\ServiceRequest;

use App\Entity\ServiceRequest;
use Symfony\Component\Uid\Uuid;

/**
 * @see CreateServiceRequestType
 * @see CreateServiceRequestCommandHandler
 */
final class CreateServiceRequestCommand
{
    /**
     * @see ServiceRequest
     */
    public function __construct(
        // the product id
        public readonly Uuid $productId,

        // the borrower user id
        public readonly Uuid $recipientId,

        public readonly \DateTimeImmutable $startAt,

        public readonly \DateTimeImmutable $endAt,

        public ?string $message,
     ) {
    }
}
