<?php

declare(strict_types=1);

namespace App\Validator\Constraints\ServiceRequest;

use App\Entity\Group;
use App\Entity\ServiceRequest;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Service request start and end dates should not conflict with the product unavalabilities.
 */
class ProductAvailabilityNoOverlapValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductAvailabilityNoOverlap) {
            throw new UnexpectedTypeException($constraint, ProductAvailabilityNoOverlap::class);
        }

        if (!$value instanceof ServiceRequest) {
            throw new UnexpectedValueException($value, Group::class);
        }

        // these dates can't be chosen for the request
        $unavailabilities = $value->getProduct()->getUnavailabilities($value);

        // compute effective dates for the new service request
        $srPeriod = CarbonInterval::days(1)->toPeriod($value->getStartAt(), $value->getEndAt());
        $srDays = array_map(static fn (CarbonInterface $date) => $date->format('Y-m-d'), $srPeriod->toArray());

        // error if there is at least one day that overlap
        if (\count(array_intersect($unavailabilities, $srDays)) > 0) {
            $this->context->buildViolation($constraint->message)
                ->atPath('startAt')
                ->addViolation();
        }
    }
}
