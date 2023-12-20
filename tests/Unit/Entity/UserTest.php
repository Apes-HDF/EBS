<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Group;
use App\Entity\Payment;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Enum\User\UserType;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UserTest extends TestCase
{
    public function testUserUnit(): void
    {
        $user = new User();
        $id = Uuid::v6();
        self::assertSame($id, $user->setId($id)->getId());
        self::assertNull($user->getType());
        self::assertTrue($user->setMainAdminAccount(true)->isMainAdminAccount());
        self::assertTrue($user->setDevAccount(true)->isDevAccount());
        $user->eraseCredentials();

        $user->setType(UserType::USER);
        self::assertSame('firstname', $user->setFirstname('firstname')->getDisplayName());
        $user->setFirstname('x');

        $user->setType(UserType::PLACE);
        self::assertSame('name', $user->setName('name')->getDisplayName());
        $user->setName('x');

        $user->setType(UserType::ADMIN);
        self::assertSame('admin', $user->setFirstname('admin')->getDisplayName());

        $group = new Group();
        $userGroup = (new UserGroup())
            ->setGroup($group);

        self::assertCount(0, $user->getUserGroups());
        $user->addUserGroup($userGroup);
        self::assertCount(1, $user->getRoles());
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertCount(1, $user->getUserGroups());
        self::assertSame([$group], $user->getMyGroups()->toArray());
        self::assertSame([$group], $user->getMyGroups()->toArray()); // with local cache

        $user->removeUserGroup($userGroup);
        self::assertCount(0, $user->getUserGroups());

        self::assertCount(0, $user->getPayments());
        $payment = new Payment();
        $user->setPayments(new ArrayCollection([$payment]));
        self::assertCount(1, $user->getPayments());

        // test exception case for getPhone.
        $user->setPhoneNumber('foobar');
        self::assertNull($user->getPhone());
    }
}
