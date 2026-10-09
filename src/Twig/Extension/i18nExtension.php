<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Symfony\Component\String\UnicodeString;
use Twig\Attribute\AsTwigFilter;

use function Symfony\Component\String\u;

class i18nExtension
{
    /**
     * Convert a Twig template name to a i18n prefix to use in XLIFF files.
     */
    #[AsTwigFilter(name: 'i18n_prefix')]
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
