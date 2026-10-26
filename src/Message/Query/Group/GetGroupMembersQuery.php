<?php

declare(strict_types=1);

namespace App\Message\Query\Group;

use App\MessageHandler\Query\Group\GetGroupMembersHandler;
use Symfony\Component\Uid\Uuid;

/**
 * @see GetGroupMembersHandler
 */
final readonly class GetGroupMembersQuery
{
    public function __construct(
        // the group uuid
        public Uuid $id,
        public ?string $memberName,
    ) {
    }
}
