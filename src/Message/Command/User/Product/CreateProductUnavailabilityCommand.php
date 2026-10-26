<?php

declare(strict_types=1);

namespace App\Message\Command\User\Product;

use App\MessageHandler\Command\Product\CreateProductAvailabilityHandler;
use Symfony\Component\Uid\Uuid;

/**
 * @see CreateProductAvailabilityHandler
 */
final readonly class CreateProductUnavailabilityCommand
{
    public function __construct(
        public Uuid $productId,

        public \DateTimeImmutable $startAt,

        public \DateTimeImmutable $endAt,
    ) {
    }
}
