<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User\Group;

use App\Doctrine\Manager\UserManager;
use App\Message\Command\User\Group\AcceptGroupInvitationCommand;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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

        // only a pending invitation can be accepted (a member or an admin keeps his status)
        $membership = $user->getGroupMembership($group);
        if ($membership === null || !$membership->getMembership()->isInvited()) {
            throw new UnprocessableEntityHttpException('Invitation not found.');
        }

        // same rule as JoinGroupCommandHandler: the invited user must pay the offer
        // of a charged group (the membership is then confirmed by the payment)
        if ($group->hasActiveOffers()) {
            throw new AccessDeniedHttpException('Group has paying offers, the user must pay.');
        }

        $membership->setMember();
        $this->userManager->save($user, true);
    }
}
