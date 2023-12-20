<?php

declare(strict_types=1);

namespace App\Enum\Product;

use App\Enum\AsArrayTrait;

enum ProductStatus: string
{
    use AsArrayTrait;

    case ACTIVE = 'active'; // is visible and can be lend
    case PAUSED = 'paused'; // visible only by owner
    case DELETED = 'deleted'; // logically deleted by owner (still visible par admins)

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isPaused(): bool
    {
        return $this === self::PAUSED;
    }

    public function isIndexable(): bool
    {
        return $this->isActive();
    }
}
