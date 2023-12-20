<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\Group;

use App\Message\Query\Group\GetGroupMembersQuery;
use App\Repository\GroupRepository;
use App\Repository\UserGroupRepository;
use Doctrine\ORM\Query;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetGroupMembersHandler
{
    public function __construct(
        public readonly UserGroupRepository $userGroupRepository,
        public readonly GroupRepository $groupRepository,
    ) {
    }

    public function __invoke(GetGroupMembersQuery $message): Query
    {
        $group = $this->groupRepository->get($message->id);

        return $this->userGroupRepository->getGroupMembers($group, $message->memberName);
    }
}
