<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GroupOfferRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::GROUP_OFFER_COUNT;

    public function testGroupOfferRepository(): void
    {
        self::bootKernel();
        $repo = $this->getGroupOfferRepository();
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);
    }
}
