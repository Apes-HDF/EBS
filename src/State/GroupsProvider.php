<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Group;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;

/**
 * @implements ProviderInterface<Group>
 */
class GroupsProvider implements ProviderInterface
{
    public function __construct(
        readonly private GroupRepository $groupRepository,
        readonly private UserRepository $userRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (isset($context['filters']['user'])) { // @phpstan-ignore-line
            $user = $this->userRepository->find($context['filters']['user']);

            $admin = (bool) ($context['filters']['admin'] ?? true); // @phpstan-ignore-line

            return $this->groupRepository->getGroupsByEnabledServices($context['filters']['services_enabled'] === 'true', $user, $admin); // @phpstan-ignore-line
        }

        return $this->groupRepository->getGroupsByEnabledServices($context['filters']['services_enabled'] === 'true'); // @phpstan-ignore-line
    }
}
