<?php

declare(strict_types=1);

namespace App\Tests\Unit\Helper;

use App\Helper\StringHelper;
use PHPUnit\Framework\TestCase;

final class StringHelperTest extends TestCase
{
    public function testHumanize(): void
    {
        $stringHelper = new StringHelper();
        self::assertSame('UUID', $stringHelper->humanize('UUID')); // not converted
        self::assertSame('Login At', $stringHelper->humanize('loginAt'));
        self::assertSame('Firstname', $stringHelper->humanize('firstname'));
    }
}
