<?php

declare(strict_types=1);

namespace App\Message\Command\User\Group;

use Symfony\Component\Uid\Uuid;

/**
 * @see UserGroupController
 * @see JoinGroupCommandHandler
 */
final class JoinGroupCommand
{
    public function __construct(
        public readonly Uuid $groupId,
        public readonly Uuid $userId,
    ) {
    }
}
