<?php

declare(strict_types=1);

namespace App\Tests\Integration\Twig\Extension;

use App\Entity\Menu;
use App\Test\ContainerTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MenuExtensionTest extends KernelTestCase
{
    use ContainerTrait;

    /**
     * Better coverage.
     */
    public function testMenuExtension(): void
    {
        self::bootKernel();
        $menuExtension = $this->getMenuExtension();
        $menu = new Menu();
        $menu->setLogo('logo.png');
        $publicUrl = $menuExtension->getPublicUrl($menu);
        self::assertSame('/storage/uploads/logo.png', $publicUrl);
    }
}
