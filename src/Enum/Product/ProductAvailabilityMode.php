<?php

declare(strict_types=1);

namespace App\Enum\Product;

use App\Enum\AsArrayTrait;

enum ProductAvailabilityMode: string
{
    use AsArrayTrait;

    case AVAILABLE = 'available';
    case UNAVAILABLE = 'unavailable';

    public function isUnavailable(): bool
    {
        return $this === self::UNAVAILABLE;
    }
}
