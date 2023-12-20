<?php

declare(strict_types=1);

namespace App\Doctrine\Listener;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;

use function Symfony\Component\String\u;

/**
 * This listener allows to use the same logic when creating a user from the frontend
 * and with EasyAdmin. If this class become too big only raise new events and handle
 * them in a dedicated subscriber.
 *
 * https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#entity-listeners-class
 */
final class UserListener
{
    public function __construct(
        private readonly UserManager $userManager
    ) {
    }

    /**
     * Not preUpdate because the plainPassword is a virtual field and doesn't trigger
     * a changeset.
     *
     * @see AbstractUserCrudController
     */
    public function preFlush(User $user): void
    {
        $plainPassword = u($user->getPlainPassword());
        if ($plainPassword->isEmpty()) {
            return;
        }

        $this->userManager->updatePassword($user);
    }

    /**
     * Normalization stuff.
     */
    public function preUpdate(User $user): void
    {
        $this->normalize($user);
    }

    /**
     * Normalization stuff.
     */
    public function prePersist(User $user): void
    {
        $this->normalize($user);
    }

    /**
     * Common normalize method.
     */
    private function normalize(User $user): void
    {
        $this->userManager->normalizeEmail($user, $user->getEmail());
    }
}
