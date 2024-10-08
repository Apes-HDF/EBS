<?php

declare(strict_types=1);

namespace App\Validator\Constraints\User;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MembershipPaid extends Constraint
{
    public string $message = 'validator.user.membership_paid';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
