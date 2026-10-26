<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Uid\Uuid;

/**
 * Thrown if the confirmation token of a user is expired.
 */
final class UserConfirmationTokenExpiredException extends \DomainException
{
    public function __construct(/**
     * The user uuid.
     */
        public Uuid $id)
    {
        parent::__construct('The email confirmation token is expired for this user');
    }
}
