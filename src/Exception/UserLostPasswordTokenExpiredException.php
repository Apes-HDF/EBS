<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown if the lost password of a user is expired.
 */
final class UserLostPasswordTokenExpiredException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The lost password token is expired for this user');
    }
}
