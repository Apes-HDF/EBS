<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\GroupResource;
use App\Repository\GroupRepository;

/**
 * @implements ProviderInterface<GroupResource>
 */
final class GroupGetStatsProvider implements ProviderInterface
{
    public function __construct(
        readonly private GroupRepository $groupRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null // @phpstan-ignore-line
    {
        $groupStats = new GroupResource();
        $groupStats->count = $this->groupRepository->count([]);

        return $groupStats;
    }
}
