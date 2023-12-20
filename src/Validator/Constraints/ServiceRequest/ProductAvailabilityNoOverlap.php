<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ServiceRequest;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ProductAvailabilityNoOverlap extends Constraint
{
    public string $message = 'validator.product.productavailabilitynooverlap';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
