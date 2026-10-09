<?php

declare(strict_types=1);

namespace App\Message\Query\User\ServiceRequest;

use Symfony\Component\Uid\Uuid;

/**
 * @see GetServiceRequestByIdQueryHandler
 */
final readonly class GetServiceRequestByIdQuery
{
    public function __construct(
        // the service request uuid
        public Uuid $id,
    ) {
    }
}
