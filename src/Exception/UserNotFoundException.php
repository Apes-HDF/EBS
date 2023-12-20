<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown if a user is not found with a given confirmation token.
 */
final class UserNotFoundException extends \DomainException
{
    public function __construct(string $token)
    {
        parent::__construct("User with confirmation token $token not found.");
    }
}
