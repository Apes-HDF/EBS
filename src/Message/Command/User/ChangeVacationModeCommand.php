<?php

declare(strict_types=1);

namespace App\Message\Command\User;

use Symfony\Component\Uid\Uuid;

final readonly class ChangeVacationModeCommand
{
    public function __construct(
        // the user id
        public Uuid $id,
    ) {
    }
}
