<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown if the token to confirm the new email of a user is expired.
 */
final class UserPendingEmailTokenExpiredException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The pending email token is expired for this user');
    }
}
