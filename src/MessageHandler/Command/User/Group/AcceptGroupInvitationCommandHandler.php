<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User\Group;

use App\Doctrine\Manager\UserManager;
use App\Message\Command\User\Group\AcceptGroupInvitationCommand;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AcceptGroupInvitationCommandHandler
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly UserRepository $userRepository,
        private readonly UserManager $userManager,
    ) {
    }

    public function __invoke(AcceptGroupInvitationCommand $message): void
    {
        $group = $this->groupRepository->get($message->groupId);
        $user = $this->userRepository->get($message->userId);

        $membership = $user->getGroupMembership($group);
        if ($membership === null) {
            throw new UnprocessableEntityHttpException('Membership not found.');
        }

        $membership->setMember();
        $this->userManager->save($user, true);
    }
}
