<?php

declare(strict_types=1);

namespace App\Validator\Constraints\User;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class UniqueUser extends Constraint
{
    public string $message = 'validator.user.unique.message';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
