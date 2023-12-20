<?php

declare(strict_types=1);

namespace App\Exception\ServiceRequest;

use Symfony\Component\Uid\Uuid;

/**
 * Thrown if a service request with a given is not found in the database (it was deleted
 * for example).
 */
final class ServiceRequestNotFoundException extends \DomainException
{
    public function __construct(Uuid $id)
    {
        parent::__construct("Service request with id $id not found.");
    }
}
