<?php

declare(strict_types=1);

namespace App\Message\Command\User\Group;

use Symfony\Component\Uid\Uuid;

use function Symfony\Component\String\u;

/**
 * @see QuitGroupCommandHandler
 * @see UserGroupController
 * @see EndMembershipCommandTest
 */
final class QuitGroupCommand
{
    // private const PUBLIC = 'public'; // put products has public
    public const VACATION = 'vacation'; // put products in vacation mode

    public function __construct(
        public readonly Uuid $groupId,
        public readonly Uuid $userId,
        /**
         * If the user has products in this group, it tells if he want to put its
         * product as public or in vacation mode.
         */
        public readonly ?string $type,
    ) {
    }

    public function isVacation(): bool
    {
        return $this->type === self::VACATION;
    }

    public function hasType(): bool
    {
        return !u($this->type)->isEmpty();
    }
}
