<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\MenuItem;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MenuItemRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::MENU_ITEMS_COUNT;

    public function testMenuRepository(): void
    {
        self::bootKernel();
        $repo = $this->getMenuItemRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $menuItems = new MenuItem();
        $menuItems->setName('name');
        $menuItems->setLink('link');
        $repo->save($menuItems, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $repo->remove($menuItems, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);
    }
}
