<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Group;
use App\Entity\User;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @implements ProviderInterface<Group>
 */
class GroupsProvider implements ProviderInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly UserRepository $userRepository,
        private readonly Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (isset($context['filters']['user'])) { // @phpstan-ignore-line
            $user = $this->userRepository->find($context['filters']['user']);

            // Only group admins can list the groups of another user
            $currentUser = $this->security->getUser();
            if ($user !== $currentUser && !$this->security->isGranted(User::ROLE_GROUP_ADMIN)) {
                throw new AccessDeniedException();
            }

            $admin = (bool) ($context['filters']['admin'] ?? true); // @phpstan-ignore-line

            return $this->groupRepository->getGroupsByEnabledServices($context['filters']['services_enabled'] === 'true', $user, $admin); // @phpstan-ignore-line
        }

        return $this->groupRepository->getGroupsByEnabledServices($context['filters']['services_enabled'] === 'true'); // @phpstan-ignore-line
    }
}
