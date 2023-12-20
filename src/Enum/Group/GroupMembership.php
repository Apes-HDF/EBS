<?php

declare(strict_types=1);

namespace App\Enum\Group;

use App\Enum\AsArrayTrait;

enum GroupMembership: string
{
    use AsArrayTrait;

    case CHARGED = 'charged'; // not free :)
    case FREE = 'free';

    public function isCharged(): bool
    {
        return $this === self::CHARGED;
    }

    public function isFree(): bool
    {
        return $this === self::FREE;
    }
}
