<?php

declare(strict_types=1);

namespace App\Message\Command\User;

use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @see ConfirmChangeLoginCommandHandler
 */
final class ConfirmChangeLoginCommand
{
    public function __construct(
        // the id of the connected user
        public readonly Uuid $id,
        // the token received at the new email
        public readonly string $token,
    ) {
        Assert::stringNotEmpty($token);
    }
}
