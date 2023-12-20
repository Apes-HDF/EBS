<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductType;
use App\Enum\Product\ProductVisibility;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProductRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::PRODUCTS_COUNT;

    public function testProductRepository(): void
    {
        self::bootKernel();
        $repo = $this->getProductRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $product = new Product();
        $product->setType(ProductType::OBJECT);
        $product->setStatus(ProductStatus::ACTIVE);
        $product->setVisibility(ProductVisibility::PUBLIC);
        /** @var User $user */
        $user = $this->getUserRepository()->find(TestReference::ADMIN_LOIC);
        $product->setOwner($user);

        /** @var Category $category */
        $category = $this->getCategoryRepository()->find(TestReference::CATEGORY_OBJECT_1);

        $product->setCategory($category);
        $product->setName('prd');
        $product->setAge('récent');

        $repo->save($product, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $repo->remove($product, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);
    }

    public function testDeleteProduct(): void
    {
        $repo = $this->getProductRepository();
        self::assertSame(self::COUNT, $repo->count([]));
        $product = $repo->get(TestReference::OBJECT_LOIC_2);
        $repo->remove($product, true);
        self::assertSame(self::COUNT - 1, $repo->count([]));
    }

    public function testGetProducts(): void
    {
        $repo = $this->getProductRepository();
        self::assertNotEmpty($repo->getObjects()->getArrayResult());
        self::assertNotEmpty($repo->getServices()->getArrayResult());
    }
}
