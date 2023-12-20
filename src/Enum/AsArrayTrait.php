<?php

declare(strict_types=1);

namespace App\Enum;

trait AsArrayTrait
{
    /**
     * @return array<string,string>
     */
    public static function getAsArray(): array
    {
        return array_reduce(
            self::cases(),
            static fn (array $choices, self $type) => $choices + [$type->name => $type->value],
            [],
        );
    }
}
