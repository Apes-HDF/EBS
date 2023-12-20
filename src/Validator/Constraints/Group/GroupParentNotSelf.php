<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Group;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class GroupParentNotSelf extends Constraint
{
    public string $message = 'validator.group.groupparentnotself';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
