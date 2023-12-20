<?php

declare(strict_types=1);

namespace App\Tests\Integration\Doctrine;

use App\Entity\User;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\String\ByteString;

final class UserManagerTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::USER_COUNT;

    public function testRemove(): void
    {
        self::bootKernel();
        $userManager = $this->getUserManager();
        $userRepo = $this->getUserRepository();

        $user = new User();
        $user->setEmail(ByteString::fromRandom(6)->toString().'@example.com');
        $user->setPassword('foo');

        $userManager->save($user, true);
        $count = $userRepo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $userManager->remove($user, true);
        $count = $userRepo->count([]);
        self::assertSame(self::COUNT, $count);
    }
}
