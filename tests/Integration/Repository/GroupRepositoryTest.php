<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Group;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GroupRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::GROUP_COUNT;

    /**
     * Test auto-generated code.
     */
    public function testGroupRepository(): void
    {
        self::bootKernel();
        $repo = $this->getGroupRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $group = new Group();
        $group->setName('grp');
        $repo->save($group, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $repo->remove($group, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $child = new Group();
        $child->setName('child');

        $group->addChild($child);
        self::assertTrue($group->getChildren()->contains($child));
        $group->removeChild($child);
        self::assertFalse($group->getChildren()->contains($child));
    }
}
