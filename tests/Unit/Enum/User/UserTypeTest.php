<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum\User;

use App\Enum\User\UserType;
use PHPUnit\Framework\TestCase;

final class UserTypeTest extends TestCase
{
    public function testUserType(): void
    {
        $typesAsArray = UserType::getAsArray();
        self::assertSame([
            'ADMIN' => 'admin',
            'USER' => 'user',
            'PLACE' => 'place',
        ], $typesAsArray);
    }
}
