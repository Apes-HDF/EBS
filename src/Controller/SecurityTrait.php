<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Webmozart\Assert\Assert;

/**
 * Shortcuts.
 */
trait SecurityTrait
{
    /**
     * getUser() like function but with the application entity typehint.
     *
     * @todo To remove to user CurrentUser attribute
     */
    public function getAppUser(): User
    {
        $user = $this->getUser();
        Assert::isInstanceOf($user, User::class, 'This function should only be called in an authenticated context (#[isGranted(User::ROLE_)])');
        /** @var User $user */

        return $user;
    }
}
