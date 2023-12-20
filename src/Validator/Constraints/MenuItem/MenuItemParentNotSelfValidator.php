<?php

declare(strict_types=1);

namespace App\Validator\Constraints\MenuItem;

use App\Entity\MenuItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * This validator checks that a menu item can not assign itself as its parent.
 */
class MenuItemParentNotSelfValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MenuItemParentNotSelf) {
            throw new UnexpectedTypeException($constraint, MenuItemParentNotSelf::class);
        }

        if (!$value instanceof MenuItem) {
            throw new UnexpectedValueException($value, MenuItem::class);
        }

        if ($this->hasForbiddenParent($value, $value->getParent())) {
            $this->context->buildViolation($constraint->message)
                ->atPath('parent')
                ->addViolation();
        }
    }

    /**
     * Check recursivly that the parent or grand parent is not equal to the current
     * menu item.
     */
    private function hasForbiddenParent(MenuItem $menuItem, ?MenuItem $parent): bool
    {
        if ($menuItem === $parent) {
            return true;
        }

        if ($parent === null) {
            return false;
        }

        return $this->hasForbiddenParent($menuItem, $parent->getParent());
    }
}
