<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\GroupOffer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GroupOfferTest extends TestCase
{
    public function testGroup(): void
    {
        $groupOffer = new GroupOffer();
        $id = Uuid::v6();
        self::assertSame($id, $groupOffer->setId($id)->getId());
        self::assertSame('EUR', $groupOffer->setCurrency('EUR')->getCurrency());
    }
}
