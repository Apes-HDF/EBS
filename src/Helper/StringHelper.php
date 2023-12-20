<?php

declare(strict_types=1);

namespace App\Helper;

use function Symfony\Component\String\u;

class StringHelper
{
    /**
     * @see https://github.com/EasyCorp/EasyAdminBundle/blob/4.x/src/Field/Configurator/CommonPreConfigurator.php
     */
    public function humanize(string $string): string
    {
        $uString = u($string);
        $upperString = $uString->upper()->toString();

        // this prevents humanizing all-uppercase labels (e.g. 'UUID' -> 'U u i d')
        // and other special labels which look better in uppercase
        if ($uString->toString() === $upperString || \in_array($upperString, ['ID', 'URL'], true)) {
            return $upperString;
        }

        return $uString
            ->replaceMatches('/([A-Z])/', '_$1')
            ->replaceMatches('/[_\s]+/', ' ')
            ->trim()
            ->lower()
            ->title(true)
            ->toString();
    }

    /**
     * Make the stored email consistent.
     */
    public function normalizeEmail(string $email): string
    {
        return u($email)->trim()->lower()->toString();
    }
}
