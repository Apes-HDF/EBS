<?php

declare(strict_types=1);

namespace App\Message\Command\Security;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see LostPasswordFormType
 */
final class LostPasswordCommand
{
    #[Assert\Email]
    #[Assert\NotBlank]
    public ?string $email = null;
}
