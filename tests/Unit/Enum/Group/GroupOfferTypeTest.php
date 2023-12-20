<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum\Group;

use App\Enum\Group\GroupOfferType;
use PHPUnit\Framework\TestCase;

final class GroupOfferTypeTest extends TestCase
{
    public function testGroupOfferType(): void
    {
        self::assertTrue(GroupOfferType::MONTHLY->isMonthly());
        self::assertTrue(GroupOfferType::YEARLY->isYearly());
    }
}
