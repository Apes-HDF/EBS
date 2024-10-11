<?php

declare(strict_types=1);

namespace App\Validator\Constraints\User;

use App\Entity\User;
use App\Enum\OfferType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MembershipPaidValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MembershipPaid) {
            throw new UnexpectedTypeException($constraint, MembershipPaid::class);
        }

        if (!$value instanceof User) {
            throw new UnexpectedValueException($value, User::class);
        }

        if (!$value->isMembershipPaid()) {
            return;
        }

        $platformOffer = $value->getPlatformOffer();
        if (null === $platformOffer) {
            $this->context->buildViolation($constraint->message)
                ->atPath('platformOffer')
                ->addViolation();

            return;
        }
        if (null === $value->getStartAt()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('startAt')
                ->addViolation();
        }

        match ($platformOffer->getType()) {
            OfferType::YEARLY, OfferType::MONTHLY => $this->checkEndAt($value, $constraint),
            OfferType::ONESHOT => null,
        };
    }

    private function checkEndAt(User $value, MembershipPaid $constraint): void
    {
        if (null === $value->getEndAt()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('endAt')
                ->addViolation();
        }
    }
}
