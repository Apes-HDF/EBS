<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    // PHP sets up to the minimum version required in composer.json
    ->withPhpSets()
    ->withAttributesSets(symfony: true, doctrine: true)
    // Symfony upgrade sets matching the installed symfony/* version (replaces SymfonySetList::SYMFONY_7x)
    ->withComposerBased(symfony: true)
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
;
