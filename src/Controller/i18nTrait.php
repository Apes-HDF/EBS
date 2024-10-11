<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\String\UnicodeString;

use function Symfony\Component\String\u;

trait i18nTrait
{
    /**
     * Get the i18n prefix of a given class to have a consistent key naming in i18n
     * files.
     */
    public function getI18nPrefix(?string $class = null): string
    {
        $class = $class ?? $this::class;

        // get an array for folders base on the class namespace
        $hierarchy = u($class)->split('\\');

        // apply snake case on each entry (which also applies lower)
        $hierarchy = array_map(static fn (UnicodeString $string) => $string->snake()->toString(), $hierarchy);

        // then join the folders with a dot
        return implode('.', $hierarchy);
    }
}
