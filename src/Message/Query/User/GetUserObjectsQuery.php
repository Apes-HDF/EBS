<?php

declare(strict_types=1);

namespace App\Message\Query\User;

use Symfony\Component\Uid\Uuid;

/**
 * @see GetUserObjectsQueryHandler
 */
final readonly class GetUserObjectsQuery
{
    public function __construct(
        public Uuid $id,
        public ?Uuid $categoryId,
    ) {
    }
}
