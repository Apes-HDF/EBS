<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Security;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Mailer\AppMailer;
use App\Mailer\Email\Security\CreateAccountStep1Email;
use App\Message\Command\Security\AccountCreateStep2RefreshCommand;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
/**
 * This allows to refresh the confirmation token of a user if he tries to confirm
 * its email and its confirmation token is already expired.
 */
final class AccountCreateStep2RefreshCommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
        private readonly AppMailer $appMailer,
    ) {
    }

    public function __invoke(AccountCreateStep2RefreshCommand $message): void
    {
        $user = $this->userRepository->find($message->id);
        Assert::isInstanceOf($user, User::class);
        $this->userManager->refreshConfirmationToken($user);
        $this->userManager->save($user, true);
        $this->appMailer->send(CreateAccountStep1Email::class, compact('user'));
    }
}
