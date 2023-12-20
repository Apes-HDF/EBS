<?php

declare(strict_types=1);

namespace App\Message\Command\Group;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see CreateGroupInvitationMessageHandler
 */
final class CreateGroupInvitationMessage
{
    public function __construct(
        #[Assert\NotBlank()]
        #[Assert\Email()]
        public ?string $email,

        public Uuid $groupId,
    ) {
    }
}
