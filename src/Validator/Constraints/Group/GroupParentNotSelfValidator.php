<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Group;

use App\Entity\Group;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * This validator checks that a group can not assign itself as its parent. it may
 * be obvious but it is allowed by doctrine and EasyAdmin.
 */
class GroupParentNotSelfValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof GroupParentNotSelf) {
            throw new UnexpectedTypeException($constraint, GroupParentNotSelf::class);
        }

        if (!$value instanceof Group) {
            throw new UnexpectedValueException($value, Group::class);
        }

        if ($this->hasForbiddenParent($value, $value->getParent())) {
            $this->context->buildViolation($constraint->message)
                ->atPath('parent')
                ->addViolation();
        }
    }

    /**
     * Check recursivly that the parent or grand parent is not equal to the current
     * group.
     */
    private function hasForbiddenParent(Group $group, ?Group $parent): bool
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
