<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\UserGroup;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserGroupRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::USER_GROUP_COUNT;

    public function testUserGroupRepository(): void
    {
        self::bootKernel();
        $repo = $this->getUserGroupRepository();
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $userGroup = new UserGroup();
        $repo->remove($userGroup, true);
    }
}
