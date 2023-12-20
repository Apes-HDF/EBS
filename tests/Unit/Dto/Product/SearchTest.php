<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto\Product;

use App\Dto\Product\Search;
use App\Entity\Address;
use PHPUnit\Framework\TestCase;

final class SearchTest extends TestCase
{
    public function testSearh(): void
    {
        $searchDto = new Search('');
        self::assertFalse($searchDto->hasQuery());
        $searchDto->q = 'foobar';
        self::assertTrue($searchDto->hasQuery());

        self::assertFalse($searchDto->hasProximity());
        self::assertFalse($searchDto->hasDistance());

        $searchDto->distance = 5;
        self::assertTrue($searchDto->hasDistance());
        self::assertFalse($searchDto->hasProximity());

        $address = new Address();
        self::assertFalse($address->hasLocality());
        $address->setLocality('Lille');
        self::assertTrue($address->hasLocality());
        $searchDto->city = $address;
        self::assertTrue($searchDto->hasProximity());
    }
}
