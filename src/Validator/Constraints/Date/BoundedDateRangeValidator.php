<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Date;

use App\Entity\ProductAvailability;
use Carbon\CarbonImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class BoundedDateRangeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof BoundedDateRange) {
            throw new UnexpectedTypeException($constraint, BoundedDateRange::class);
        }

        [$startAt, $endAt] = self::getRange($value);
        if ($startAt === null || $endAt === null) {
            return; // NotBlank / type errors are reported by the fields
        }

        // property path of the end date: array data (unmapped form) or object
        $path = \is_array($value) ? '[endAt]' : 'endAt';
        if ($endAt < $startAt) {
            $this->context->buildViolation($constraint->orderMessage)->atPath($path)->addViolation();

            return;
        }

        if (!ProductAvailability::isWithinHorizon($startAt, $endAt)) {
            $this->context->buildViolation($constraint->horizonMessage)
                ->setParameter('{{ max }}', CarbonImmutable::today()->addDays(ProductAvailability::MAX_DAYS)->format('d/m/Y'))
                ->atPath($path)
                ->addViolation();
        }
    }

    /**
     * @return array{?\DateTimeInterface, ?\DateTimeInterface}
     */
    private static function getRange(mixed $value): array
    {
        if (\is_array($value)) {
            $startAt = $value['startAt'] ?? null;
            $endAt = $value['endAt'] ?? null;
        } elseif (\is_object($value) && method_exists($value, 'getStartAt') && method_exists($value, 'getEndAt')) {
            try {
                $startAt = $value->getStartAt();
                $endAt = $value->getEndAt();
            } catch (\Error) {
                // typed properties not initialized: the field errors are reported
                return [null, null];
            }
        } else {
            return [null, null];
        }

        return [
            $startAt instanceof \DateTimeInterface ? $startAt : null,
            $endAt instanceof \DateTimeInterface ? $endAt : null,
        ];
    }
}
