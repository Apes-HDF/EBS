<?php

declare(strict_types=1);

namespace App\Enum\Product;

use App\Enum\AsArrayTrait;

enum ProductVisibility: string
{
    use AsArrayTrait;

    case PUBLIC = 'public'; // is visible by everyone and any group
    case RESTRICTED = 'restricted'; // is visible only by people belonging to some groups

    public function isPublic(): bool
    {
        return $this === self::PUBLIC;
    }
}
