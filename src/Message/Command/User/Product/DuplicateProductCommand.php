<?php

declare(strict_types=1);

namespace App\Message\Command\User\Product;

use Symfony\Component\Uid\Uuid;

/**
 * @see NewMessageType
 * @see DuplicateProductCommandHandler
 */
final class DuplicateProductCommand
{
    public function __construct(
        // related product
        public readonly Uuid $productId,

        // optionnal attribute to test
        public readonly ?string $attribute,
    ) {
    }
}
