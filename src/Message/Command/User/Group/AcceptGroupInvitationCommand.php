<?php

declare(strict_types=1);

namespace App\Message\Command\User\Group;

use Symfony\Component\Uid\Uuid;

/**
 * @see UserGroupController::acceptInvitation()
 * @see AcceptGroupInvitationCommandHandler
 */
final readonly class AcceptGroupInvitationCommand
{
    public function __construct(
        public Uuid $groupId,
        public Uuid $userId,
    ) {
    }
}
