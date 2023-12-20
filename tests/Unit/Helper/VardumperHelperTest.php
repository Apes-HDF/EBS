<?php

declare(strict_types=1);

namespace App\Tests\Unit\Helper;

use App\Helper\VarDumperHelper;
use PHPUnit\Framework\TestCase;

final class VardumperHelperTest extends TestCase
{
    public function testforceCli(): void
    {
        VarDumperHelper::forceCli();
        self::assertSame('dd', dump('dd')); // @phpstan-ignore-line
        ob_clean();
    }
}
