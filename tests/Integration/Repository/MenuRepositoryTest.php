<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Menu;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MenuRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::MENU_COUNT;

    public function testMenuRepository(): void
    {
        self::bootKernel();
        $repo = $this->getMenuRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $menu = new Menu();
        $menu->setLogo('logo');
        $menu->setCode('menu_left');
        $repo->save($menu, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $repo->remove($menu, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);
    }
}
