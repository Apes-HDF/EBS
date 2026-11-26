<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Date;

use Symfony\Component\Validator\Constraint;

/**
 * The startAt / endAt range of a form (object or array data) must be in the
 * right order and within the booking horizon.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class BoundedDateRange extends Constraint
{
    public string $orderMessage = 'validator.date_range.order';
    public string $horizonMessage = 'validator.date_range.horizon';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
