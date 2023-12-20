<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto\Admin;

use App\Message\Command\Admin\AbstractFormCommand;

final class DummyFormCommand extends AbstractFormCommand
{
    public ?string $wrongSectionProperty = null; // wrong section for field, only dummySection is alloawed

    /**
     * @return array<string>
     */
    protected function getSections(): array
    {
        return [
            'dummySection',
        ];
    }
}
