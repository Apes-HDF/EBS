<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\Group;

use App\Entity\Group;
use App\Exception\Group\GroupNotFoundException;
use App\Message\Query\Group\GetGroupByIdQuery;
use App\Repository\GroupRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetGroupByIdQueryHandler
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
    ) {
    }

    public function __invoke(GetGroupByIdQuery $message): Group
    {
        $group = $this->groupRepository->find($message->id);
        if ($group === null) {
            throw new GroupNotFoundException($message->id);
        }

        return $group;
    }
}
