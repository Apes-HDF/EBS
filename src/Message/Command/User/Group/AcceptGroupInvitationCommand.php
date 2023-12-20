<?php

declare(strict_types=1);

namespace App\Message\Command\User\Group;

use Symfony\Component\Uid\Uuid;

/**
 * @see UserGroupController::acceptInvitation()
 * @see AcceptGroupInvitationCommandHandler
 */
final class AcceptGroupInvitationCommand
{
    public function __construct(
        public readonly Uuid $groupId,
        public readonly Uuid $userId,
    ) {
    }
}
