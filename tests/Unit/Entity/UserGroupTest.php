<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Enum\Group\UserMembership;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UserGroupTest extends TestCase
{
    public function testUserGroup(): void
    {
        $userGroup = new UserGroup();
        $id = Uuid::v6();
        self::assertSame($id, $userGroup->setId($id)->getId());
        $user = new User();
        self::assertSame($user, $userGroup->setUser($user)->getUser());
        $group = new Group();
        $date = new \DateTimeImmutable('now');
        self::assertSame($group, $userGroup->setGroup($group)->getGroup());
        self::assertSame(UserMembership::ADMIN, $userGroup->setMembership(UserMembership::ADMIN)->getMembership());

        // todelete when payment tests are done
        $payedAt = new \DateTimeImmutable('now');
        self::assertSame($payedAt, $userGroup->setPayedAt($payedAt)->getPayedAt());
    }

    public function testSetMember(): void
    {
        $userGroup = new UserGroup();
        self::assertSame(UserMembership::INVITATION, $userGroup->getMembership());

        $userGroup->setMember();
        self::assertSame(UserMembership::MEMBER, $userGroup->getMembership());

        $userGroup->setMembership(UserMembership::ADMIN);
        $userGroup->setMember();
        // must stay admin
        self::assertTrue($userGroup->getMembership()->isAdmin());
    }
}
