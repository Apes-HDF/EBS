<?php

declare(strict_types=1);

namespace App\Security\Checker;

use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AuthorizationChecker
{
    public function __construct(
        public readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function isAdmin(): bool
    {
        return $this->authorizationChecker->isGranted(User::ROLE_ADMIN);
    }

    /**
     * Check if the current logged user has the ADMIN role and throw an execption
     * otherwise.
     */
    public function checkAdminRole(): void
    {
        if (!$this->isAdmin()) {
            throw new AccessDeniedHttpException('Admin role is required to access this ressource.');
        }
    }

    public function hasGroupAdminRole(): bool
    {
        return $this->authorizationChecker->isGranted(User::ROLE_GROUP_ADMIN);
    }

    public function isGroupAdmin(): void
    {
        if (!$this->hasGroupAdminRole()) {
            throw new AccessDeniedHttpException('The group admin role is required to access this resource.');
        }
    }
}
