<?php

declare(strict_types=1);

namespace App\Validator\Constraints\MenuItem;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MenuItemParentNotSelf extends Constraint
{
    public string $message = 'validator.menuitem.parentnotself';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
