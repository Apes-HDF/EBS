<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum\ServiceRequest;

use App\Enum\ServiceRequest\ServiceRequestStatus;
use PHPUnit\Framework\TestCase;

final class ServiceRequestStatusTest extends TestCase
{
    public function testUserType(): void
    {
        self::assertTrue(ServiceRequestStatus::NEW->isNew());
        self::assertTrue(ServiceRequestStatus::TO_CONFIRM->isToConfirm());
        self::assertTrue(ServiceRequestStatus::CONFIRMED->isConfirmed());
        self::assertTrue(ServiceRequestStatus::REFUSED->isRefused());
        self::assertTrue(ServiceRequestStatus::FINISHED->isFinished());
    }

    public function testUserTypeIsOngoing(): void
    {
        self::assertTrue(ServiceRequestStatus::NEW->isOngoing());
        self::assertTrue(ServiceRequestStatus::TO_CONFIRM->isOngoing());
        self::assertTrue(ServiceRequestStatus::CONFIRMED->isOngoing());

        self::assertFalse(ServiceRequestStatus::REFUSED->isOngoing());
        self::assertFalse(ServiceRequestStatus::FINISHED->isOngoing());
    }
}
