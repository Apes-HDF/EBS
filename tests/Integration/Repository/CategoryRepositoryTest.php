<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Category;
use App\Enum\Product\ProductType;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CategoryRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::CATEGORIES_COUNT;

    public function testCategoryRepositoty(): void
    {
        self::bootKernel();
        $repo = $this->getCategoryRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $category = new Category();
        $category->setName('grp');
        $category->setType(ProductType::OBJECT);
        $repo->save($category, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $repo->remove($category, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $child = new Category();
        $child->setName('child');
        $child->setType(ProductType::OBJECT);

        $category->addChild($child);
        self::assertTrue($category->getChildren()->contains($child));
        $category->removeChild($child);
        self::assertFalse($category->getChildren()->contains($child));
    }
}
