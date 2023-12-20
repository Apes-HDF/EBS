# Note regarding composer.json

## Meilisearch

    meilisearch/meilisearch-php

Is locked to `v0.26.0` to prevent this error:

    Executing script cache:clear [KO]
    [KO]
    Script cache:clear returned with error code 1
    !!
    !!  In DebugClassLoader.php line 327:
    !!
    !!    Case mismatch between loaded and declared class names: "MeiliSearch\Client"
    !!     vs "Meilisearch\Client".
    !!
    !!
    !!
    Script @auto-scripts was called via post-update-cmd


I have opened a ticket https://github.com/meilisearch/meilisearch-php/issues/452.
