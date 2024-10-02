<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Security;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Mailer\AppMailer;
use App\Mailer\Email\Security\CreateAccountStep1Email;
use App\Message\Command\Security\AccountCreateStep1Command;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
/**
 * @see AccountCreateController
 */
final class AccountCreateStep1CommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly AppMailer $appMailer,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Some normalization is done in the UserListener (email). The password encoding
     * is alos done is the UserListener.
     *
     * @see UserListener
     */
    public function __invoke(AccountCreateStep1Command $message): void
    {
        $existingUser = $this->userRepository->findOneByEmail($message->email);
        if ($existingUser instanceof User) {
            if (!$existingUser->isEmailConfirmed()) {
                $this->userManager->refreshConfirmationToken($existingUser);
                $this->userManager->save($existingUser, true);
                $this->appMailer->send(CreateAccountStep1Email::class, ['user' => $existingUser]);

                return;
            }
        }

        $user = new User();
        $user->setEmail($message->email);
        $this->userManager->refreshConfirmationToken($user);
        $this->userManager->save($user, true);
        $this->appMailer->send(CreateAccountStep1Email::class, compact('user'));
    }
}
