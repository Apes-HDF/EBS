<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown if the new email of a user has been taken by another account before
 * its confirmation.
 */
final class UserPendingEmailAlreadyUsedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('The pending email is already used by another account');
    }
}
