<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProductAvailabilityRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::PRODUCT_AVAILABILITIES_COUNT;

    public function testProductAvailabilityRepository(): void
    {
        self::bootKernel();
        $repo = $this->getProductAvailabilityRepository();
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);
    }
}
