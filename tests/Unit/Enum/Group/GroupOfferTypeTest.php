<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum\Group;

use App\Enum\OfferType;
use PHPUnit\Framework\TestCase;

final class GroupOfferTypeTest extends TestCase
{
    public function testGroupOfferType(): void
    {
        self::assertTrue(OfferType::MONTHLY->isMonthly());
        self::assertTrue(OfferType::YEARLY->isYearly());
    }
}
