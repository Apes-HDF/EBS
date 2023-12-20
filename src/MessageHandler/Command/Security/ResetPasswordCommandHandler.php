<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Security;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Message\Command\Security\ResetPasswordCommand;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final class ResetPasswordCommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(ResetPasswordCommand $message): void
    {
        $user = $this->userRepository->find($message->id);
        Assert::isInstanceOf($user, User::class);
        $this->userManager->updatePassword($user->setPlainPassword($message->password));
        $user->resetLostPawword();

        // we consider the reset password also act as an email confirmation
        // we could also confirm the email as soon the user access the url with the token
        $user->confirmEmail();
        $user->resetConfirmation();

        $this->userManager->save($user, true);
    }
}
