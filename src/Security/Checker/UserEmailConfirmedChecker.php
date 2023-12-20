<?php

declare(strict_types=1);

namespace App\Security\Checker;

use App\Entity\User;
use App\Security\Exception\AccountEmailNotConfirmedException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Prevent a user to login with an unconfirmed email.
 */
#[AutoconfigureTag('security.user_checker.main', ['priority' => 10])]
class UserEmailConfirmedChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        /** @var User $user */
        if (!$user->isEmailConfirmed()) {
            throw new AccountEmailNotConfirmedException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
