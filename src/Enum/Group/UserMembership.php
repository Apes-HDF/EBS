<?php

declare(strict_types=1);

namespace App\Enum\Group;

use App\Enum\AsArrayTrait;

enum UserMembership: string
{
    use AsArrayTrait;

    case INVITATION = 'invitation'; // pending invitation to join the group.
    case MEMBER = 'member'; // member of the group.
    case ADMIN = 'admin'; // admin of the group.

    public function isInvited(): bool
    {
        return $this === self::INVITATION;
    }

    public function isMember(): bool
    {
        return $this === self::MEMBER;
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isConfirmed(): bool
    {
        return $this->isMember() || $this->isAdmin();
    }
}
