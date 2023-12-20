<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum\Group;

use App\Enum\Group\UserMembership;
use PHPUnit\Framework\TestCase;

final class UserMembershipTest extends TestCase
{
    public function testUserMembership(): void
    {
        self::assertTrue(UserMembership::INVITATION->isInvited());
        self::assertTrue(UserMembership::MEMBER->isMember());
        self::assertTrue(UserMembership::ADMIN->isAdmin());
    }
}
