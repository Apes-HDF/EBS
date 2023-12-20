<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Security;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Enum\User\UserType;
use App\Message\Command\Security\AccountCreateStep2Command;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final class AccountCreateStep2CommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(AccountCreateStep2Command $message): void
    {
        $user = $this->userRepository->find($message->id);
        Assert::isInstanceOf($user, User::class);

        $user->setType($message->type);
        switch ($message->type) {
            case UserType::USER:
                Assert::stringNotEmpty($message->firstname, 'The firstname is mandatory');
                $user->setFirstname($message->firstname);
                Assert::stringNotEmpty($message->lastname, 'The lastname is mandatory');
                $user->setLastname($message->lastname);
                $user->setName($message->name);
                break;

            case UserType::PLACE:
                $user->setFirstname(null);
                $user->setLastname(null);
                Assert::stringNotEmpty($message->name, 'The name is mandatory');
                $user->setName($message->name);
                break;

            default:
                throw new \UnexpectedValueException('This hanlder can only create users or places.');
        }

        $this->userManager->updatePassword($user->setPlainPassword($message->plainPassword));
        $this->userManager->finalizeAccountCreateStep2($user);
        $this->userManager->save($user, true);
    }
}
