<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AddressRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::ADDRESSES_COUNT;

    public function testCategoryRepository(): void
    {
        self::bootKernel();
        $repo = $this->getAddressRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);
    }
}
