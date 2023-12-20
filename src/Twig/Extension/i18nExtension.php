<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Symfony\Component\String\UnicodeString;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function Symfony\Component\String\u;

class i18nExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('i18n_prefix', $this->getI18Prefix(...)),
        ];
    }

    /**
     * Convert a Twig template name to a i18n prefix to use in XLIFF files.
     */
    public function getI18Prefix(string $temlateName): string
    {
        $temlateName = u($temlateName)->trimSuffix('.html.twig');
        $hierarchy = u($temlateName->toString())->split('/');

        // apply snake case on each entry (which also applis lower)
        $hierarchy = array_map(static fn (UnicodeString $string) => $string->snake()->toString(), $hierarchy);

        // then join the folders with a dot with the templates prefix
        return 'templates.'.implode('.', $hierarchy);
    }
}
