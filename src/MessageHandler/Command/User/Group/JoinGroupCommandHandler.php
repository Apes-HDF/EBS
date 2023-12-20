<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User\Group;

use App\Doctrine\Manager\UserManager;
use App\Message\Command\User\Group\JoinGroupCommand;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class JoinGroupCommandHandler
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly UserRepository $userRepository,
        private readonly UserManager $userManager,
    ) {
    }

    public function __invoke(JoinGroupCommand $message): void
    {
        $group = $this->groupRepository->get($message->groupId);
        $user = $this->userRepository->get($message->userId);

        // 1. test if group is public
        if (!$group->getType()->isPublic()) {
            throw new AccessDeniedHttpException('Group is not public and can only be joined with an invitation link.');
        }

        // 2. test if group is NOT free, user must pay even he is invited
        if ($group->hasActiveOffers()) {
            throw new AccessDeniedHttpException('Group has paying offers, the user must pay.');
        }

        // 3. test if the user is not already member of the group
        if ($user->hasLink($group)) {
            return;
        }

        // 4. Save in db with the member status
        $this->userManager->addToGroup($user, $group);
        $this->userManager->save($user, true);
    }
}
