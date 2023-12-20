<?php

declare(strict_types=1);

namespace App\Controller\Group;

use App\Entity\Group;
use App\Message\Query\Group\GetGroupByIdQuery;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Uid\Uuid;

trait GroupTrait
{
    private function getGroup(string $id): Group
    {
        try {
            /** @var Group $group */
            $group = $this->queryBus->query(new GetGroupByIdQuery(Uuid::fromString($id)));
        } catch (HandlerFailedException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        return $group;
    }
}
