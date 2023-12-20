<?php

declare(strict_types=1);

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

/**
 * Specific message returned if the user is not enabled.
 */
final class AccountDisabledException extends CustomUserMessageAccountStatusException
{
    public const MESSAGE_KEY = 'login.account_disabled_exception';

    public function getMessageKey(): string
    {
        return self::MESSAGE_KEY;
    }
}
