<?php

declare(strict_types=1);

namespace App\Message\Query\Group;

use App\Controller\Group\GroupController;
use Symfony\Component\Uid\Uuid;

/**
 * @see GroupController::show()
 * @see GetGroupByIdQueryHandler
 */
final class GetGroupByIdQuery
{
    public function __construct(
        // the group uuid
        public readonly Uuid $id,
    ) {
    }
}
