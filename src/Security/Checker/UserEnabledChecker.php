<?php

declare(strict_types=1);

namespace App\Security\Checker;

use App\Entity\User;
use App\Security\Exception\AccountDisabledException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Prevent a user to login with a disabled account.
 */
#[AutoconfigureTag('security.user_checker.main', ['priority' => 10])]
class UserEnabledChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        /** @var User $user */
        if (!$user->isEnabled()) {
            throw new AccountDisabledException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
