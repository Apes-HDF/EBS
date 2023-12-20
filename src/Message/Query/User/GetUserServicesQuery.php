<?php

declare(strict_types=1);

namespace App\Message\Query\User;

use Symfony\Component\Uid\Uuid;

/**
 * @see GetUserServicesQueryHandler
 */
final class GetUserServicesQuery
{
    public function __construct(
        public readonly Uuid $id,
        public readonly ?Uuid $categoryId,
    ) {
    }
}
