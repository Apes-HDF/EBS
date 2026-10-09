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
        $choices = [];
        foreach (self::cases() as $type) {
            $choices[$type->name] = $type->value;
        }

        return $choices;
    }
}
