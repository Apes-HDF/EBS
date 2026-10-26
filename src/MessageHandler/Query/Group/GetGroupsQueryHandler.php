<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\Group;

use App\Entity\Group;
use App\Message\Query\Group\GetGroupsQuery;
use App\Repository\GroupRepository;
use Doctrine\ORM\Query;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetGroupsQueryHandler
{
    public function __construct(
        private GroupRepository $groupRepository,
    ) {
    }

    /**
     * @return Query<null, Group>
     */
    public function __invoke(GetGroupsQuery $message): Query
    {
        return $this->groupRepository->getGroups($message->groupName);
    }
}
