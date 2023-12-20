<?php

declare(strict_types=1);

namespace App\Enum\Product;

use App\Enum\AsArrayTrait;

enum ProductType: string
{
    use AsArrayTrait;

    case OBJECT = 'object'; // an object to lend
    case SERVICE = 'service'; // a proposed service

    public function isObject(): bool
    {
        return $this === self::OBJECT;
    }

    public function isService(): bool
    {
        return $this === self::SERVICE;
    }
}
