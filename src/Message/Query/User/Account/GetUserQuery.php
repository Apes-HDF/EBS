<?php

declare(strict_types=1);

namespace App\Message\Query\User\Account;

use App\MessageHandler\Query\User\Account\GetUserQueryHandler;
use Symfony\Component\Uid\Uuid;

/**
 * @see GetUserQueryHandler
 */
final class GetUserQuery
{
    public function __construct(
        // the member uuid
        public readonly Uuid $id,
    ) {
    }
}
