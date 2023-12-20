<?php

declare(strict_types=1);

namespace App\Message\Command\Security;

use App\Entity\User;
use App\Enum\User\UserType;
use App\MessageHandler\Command\Security\AccountCreateStep2CommandHandler;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @see AccountCreateStep2FormType
 * @see AccountCreateStep2CommandHandler
 */
final class AccountCreateStep2Command
{
    public Uuid $id;
    public UserType $type;
    public ?string $lastname = null;
    public ?string $firstname = null;
    public ?string $name = null;
    public string $plainPassword;

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        Assert::notNull($user->getType());
        $this->type = $user->getType();
        $this->firstname = $user->getFirstname();
        $this->lastname = $user->getLastname();
        $this->name = $user->getName();
        Assert::stringNotEmpty($user->getPlainPassword());
        $this->plainPassword = $user->getPlainPassword();
    }
}
