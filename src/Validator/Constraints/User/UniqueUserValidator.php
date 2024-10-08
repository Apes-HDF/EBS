<?php

declare(strict_types=1);

namespace App\Validator\Constraints\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class UniqueUserValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueUser) {
            throw new UnexpectedTypeException($constraint, UniqueUser::class);
        }

        if (!$value instanceof User) {
            throw new UnexpectedValueException($value, User::class);
        }

        $existingUser = $this->userRepository->findOneByEmail($value->getEmail());

        if (null === $existingUser) {
            return;
        }

        if (!$existingUser->isEmailConfirmed()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath('email')
            ->addViolation();
    }
}
