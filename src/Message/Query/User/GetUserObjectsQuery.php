<?php

declare(strict_types=1);

namespace App\Message\Query\User;

use Symfony\Component\Uid\Uuid;

/**
 * @see GetUserObjectsQueryHandler
 */
final class GetUserObjectsQuery
{
    public function __construct(
        public readonly Uuid $id,
        public readonly ?Uuid $categoryId,
    ) {
    }
}
