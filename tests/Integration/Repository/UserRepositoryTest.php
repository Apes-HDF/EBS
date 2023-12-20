<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\String\ByteString;

final class UserRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::USER_COUNT;

    public function testGroupRepository(): void
    {
        self::bootKernel();
        $repo = $this->getUserRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $user = new User();
        $user->setEmail(ByteString::fromRandom(6)->toString().'@example.com');
        $user->setPassword('foo');
        $repo->save($user, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $repo->upgradePassword($user, 'foo');

        $repo->remove($user, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);
    }

    public function testUpgradePasswordException(): void
    {
        self::bootKernel();
        $repo = $this->getUserRepository();
        $user = $this->getMockBuilder(PasswordAuthenticatedUserInterface::class)->getMock();
        $this->expectException(UnsupportedUserException::class);
        $repo->upgradePassword($user, 'foo');
    }
}
