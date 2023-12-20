<?php

declare(strict_types=1);

namespace App\Tests\Integration\Twig\Extension;

use App\Entity\Category;
use App\Test\ContainerTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CategoryExtensionTest extends KernelTestCase
{
    use ContainerTrait;

    /**
     * Better coverage.
     */
    public function testCategoryExtension(): void
    {
        self::bootKernel();
        $categoryExtension = $this->getCategoryExtension();
        $category = new Category();
        $name = 'apes.png';
        $category->setImage($name);
        $publicUrl = $categoryExtension->getPublicUrl($category);
        self::assertSame('/storage/uploads/category/apes.png', $publicUrl);
    }
}
