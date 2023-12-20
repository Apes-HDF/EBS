<?php

declare(strict_types=1);

namespace App\Tests\Integration\Twig\Extension;

use App\Entity\Address;
use App\Entity\Menu;
use App\Entity\Product;
use App\Test\ContainerTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class EntityExtensionTest extends KernelTestCase
{
    use ContainerTrait;

    /**
     * Better coverage.
     */
    public function testEntityExtension(): void
    {
        self::bootKernel();
        $categoryExtension = $this->getEntityExtension();
        self::assertFalse($categoryExtension->isImagesEntity(new Address()));
        self::assertTrue($categoryExtension->isImagesEntity(new Product()));

        self::assertFalse($categoryExtension->isImageEntity(new Address()));
        self::assertTrue($categoryExtension->isImageEntity(new Menu()));
    }
}
