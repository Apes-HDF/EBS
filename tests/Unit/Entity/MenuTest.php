<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Menu;
use App\Entity\MenuItem;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class MenuTest extends TestCase
{
    public function testMenu(): void
    {
        $menu = new Menu();
        $id = 1;
        $items = new ArrayCollection();

        self::assertSame($id, $menu->setId($id)->getId());
        self::assertSame('logo', $menu->setLogo('logo')->getImage());
        self::assertSame('menu', $menu->setCode('menu')->getCode());
        self::assertSame($items, $menu->setItems($items)->getItems());

        self::assertSame(0, $menu->getItems()->count());
        $menuItem = new MenuItem();
        $menu->addItem($menuItem);
        self::assertSame(1, $menu->getItems()->count());
        $menu->removeItem($menuItem);
        self::assertSame(0, $menu->getItems()->count());
    }
}
