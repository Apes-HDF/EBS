<?php

declare(strict_types=1);

namespace App\Message\Query\Group;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;

/**
 * @see GetGroupsQueryHandler
 */
final class GetGroupsQuery
{
    // the id of the current user if logged
    public ?Uuid $userId;

    public function __construct(
        public readonly ?User $user,
        public readonly ?string $groupName,
    ) {
        $this->userId = $user?->getId();
    }
}
