<?php

declare(strict_types=1);

namespace App\Message\Command\Security;

use App\Doctrine\Manager\UserManager;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see AccountCreateFormType
 */
final class ResetPasswordCommand
{
    // the user id
    public Uuid $id;

    // the new password to save
    #[Assert\NotBlank]
    #[Assert\Length(min: UserManager::PASWWORD_MIN_LENGTH, max: UserManager::PASWWORD_MAX_LENGTH)]
    public ?string $password = null;
}
