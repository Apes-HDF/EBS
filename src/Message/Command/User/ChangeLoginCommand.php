<?php

declare(strict_types=1);

namespace App\Message\Command\User;

use Symfony\Component\Uid\Uuid;

/**
 * @see ChangeLoginCommandHandler
 */
final class ChangeLoginCommand
{
    public function __construct(
        // the user id
        public readonly Uuid $id,
        // the new email, stored as pending until it is confirmed
        public string $email,
    ) {
    }
}
