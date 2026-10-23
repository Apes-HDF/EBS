<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use App\State\GroupGetStatsProvider;

#[ApiResource(
    shortName: 'Group',
    operations: [
        new Get(
            uriTemplate: '/groups/stats',
            openapi: new Model\Operation(summary: self::DESCRIPTION),
            description: self::DESCRIPTION,
            name: 'group_get_collection_stats',
            provider: GroupGetStatsProvider::class
        ),
    ]
)]
class GroupResource
{
    final public const DESCRIPTION = 'Retrieve some stats from the group table.';

    public int $count;
}
