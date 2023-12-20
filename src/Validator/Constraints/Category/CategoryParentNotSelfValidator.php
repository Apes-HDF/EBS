<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Category;

use App\Entity\Category;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * This validator checks that a category can not assign itself as its parent.
 */
class CategoryParentNotSelfValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CategoryParentNotSelf) {
            throw new UnexpectedTypeException($constraint, CategoryParentNotSelf::class);
        }

        if (!$value instanceof Category) {
            throw new UnexpectedValueException($value, Category::class);
        }

        if ($this->hasForbiddenParent($value, $value->getParent())) {
            $this->context->buildViolation($constraint->message)
                ->atPath('parent')
                ->addViolation();
        }
    }

    /**
     * Check recursivly that the parent or grand parent is not equal to the current
     * category.
     */
    private function hasForbiddenParent(Category $group, ?Category $parent): bool
    {
        if ($group === $parent) {
            return true;
        }

        if ($parent === null) {
            return false;
        }

        return $this->hasForbiddenParent($group, $parent->getParent());
    }
}
