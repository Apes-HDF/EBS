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
        readonly private UserRepository $userRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null // @phpstan-ignore-line
    {
        if (isset($context['filters']['user'])) {
            $user = $this->userRepository->find($context['filters']['user']);

            return $this->groupRepository->getGroupsByEnabledServices($context['filters']['services_enabled'] === 'true', $user);
        }

        return $this->groupRepository->getGroupsByEnabledServices($context['filters']['services_enabled'] === 'true');
    }
}
