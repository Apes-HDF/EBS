<?php

declare(strict_types=1);

namespace App\Message\Command\Security;

use App\Entity\User;
use App\Form\Type\Security\AccountCreateStep1FormType;
use App\MessageHandler\Command\Security\AccountCreateStep1CommandHandler;
use Webmozart\Assert\Assert;

/**
 * @see AccountCreateStep1FormType
 * @see AccountCreateStep1CommandHandler
 */
final class AccountCreateStep1Command
{
    public string $email;

    public function __construct(User $user)
    {
        $email = $user->getEmail();
        Assert::stringNotEmpty($email, 'The email of a user cannot be null or empty');
        Assert::email($email, 'The email is invalid');
        $this->email = $email;
    }
}
