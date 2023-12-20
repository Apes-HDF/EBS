<?php

declare(strict_types=1);

namespace App\Tests\Integration\Doctrine\Manager;

use App\Test\ContainerRepositoryTrait;
use App\Test\ContainerTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProductManagerTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use ContainerTrait;
    use RefreshDatabaseTrait;

    public function testHasProductsOnlyInGroup(): void
    {
        self::bootKernel();
        $productManager = $this->getProductManager();
        $group = $this->getGroupRepository()->get(TestReference::GROUP_1);
        $group2 = $this->getGroupRepository()->get(TestReference::GROUP_7);
        $user = $this->getUserRepository()->get(TestReference::PLACE_APES);
        $product = $this->getProductRepository()->get(TestReference::OBJECT_PLACE_6);
        $product->addGroup($group2);
        self::assertFalse($productManager->hasProductsOnlyInGroup($group, $user));
    }
}
