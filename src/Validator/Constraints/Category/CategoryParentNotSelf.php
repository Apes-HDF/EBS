<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Category;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CategoryParentNotSelf extends Constraint
{
    public string $message = 'validator.category.parentnotself';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
