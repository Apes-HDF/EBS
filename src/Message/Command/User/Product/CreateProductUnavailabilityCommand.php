<?php

declare(strict_types=1);

namespace App\Message\Command\User\Product;

use App\MessageHandler\Command\Product\CreateProductAvailabilityHandler;
use Symfony\Component\Uid\Uuid;

/**
 * @see CreateProductAvailabilityHandler
 */
final class CreateProductUnavailabilityCommand
{
    public function __construct(
        public readonly Uuid $productId,

        public readonly \DateTimeImmutable $startAt,

        public readonly \DateTimeImmutable $endAt,
    ) {
    }
}
