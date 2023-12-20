<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto\Admin;

use PHPUnit\Framework\TestCase;

final class AbstractFormCommandTest extends TestCase
{
    public function testToJsonArrayException(): void
    {
        $command = new DummyFormCommand();
        $this->expectException(\UnexpectedValueException::class);
        $command->toJsonArray();
    }
}
