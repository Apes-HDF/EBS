<?php

declare(strict_types=1);

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

/**
 * Specific message returned if the user hasn't confirmed their email yet.
 */
final class AccountEmailNotConfirmedException extends CustomUserMessageAccountStatusException
{
    public const MESSAGE_KEY = 'login.account_email_not_confirmed_exception';

    public function getMessageKey(): string
    {
        return self::MESSAGE_KEY;
    }
}
