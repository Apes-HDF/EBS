<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Menu;
use App\Entity\MenuItem;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class MenuItemTest extends TestCase
{
    public function testMenuItems(): void
    {
        $menuItem = new MenuItem();
        $id = Uuid::v6();
        $children = new ArrayCollection();
        $menuItem2 = new MenuItem();
        $menu = new Menu();

        self::assertSame($id, $menuItem->setId($id)->getId());
        self::assertSame($menu, $menuItem->setMenu($menu)->getMenu());
        self::assertSame('first item', $menuItem->setName('first item')->getName());
        self::assertSame('link', $menuItem->setLink('link')->getLink());
        self::assertSame($menuItem2, $menuItem->setParent($menuItem2)->getParent());
        self::assertSame($children, $menuItem->setChildren($children)->getChildren());
    }
}
