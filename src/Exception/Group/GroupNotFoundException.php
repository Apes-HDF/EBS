<?php

declare(strict_types=1);

namespace App\Exception\Group;

use Symfony\Component\Uid\Uuid;

final class GroupNotFoundException extends \DomainException
{
    public function __construct(Uuid $id)
    {
        parent::__construct("Group with id $id not found.");
    }
}
