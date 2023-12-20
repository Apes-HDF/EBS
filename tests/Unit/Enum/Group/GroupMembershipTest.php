<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum\Group;

use App\Enum\Group\GroupMembership;
use PHPUnit\Framework\TestCase;

final class GroupMembershipTest extends TestCase
{
    public function testGroupMembership(): void
    {
        self::assertTrue(GroupMembership::CHARGED->isCharged());
        self::assertTrue(GroupMembership::FREE->isFree());
    }
}
