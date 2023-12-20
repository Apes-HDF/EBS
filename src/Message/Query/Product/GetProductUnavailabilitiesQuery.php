<?php

declare(strict_types=1);

namespace App\Message\Query\Product;

use App\MessageHandler\Query\Product\GetProductUnavailabilitiesQueryHandler;
use Symfony\Component\Uid\Uuid;

/** @see GetProductUnavailabilitiesQueryHandler */
final class GetProductUnavailabilitiesQuery
{
    public function __construct(
        // product unavailability id
        public readonly Uuid $id,
    ) {
    }
}
