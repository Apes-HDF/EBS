<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Mailer\AppMailer;
use App\Mailer\Email\Security\ChangeLoginEmail;
use App\Message\Command\User\ChangeLoginCommand;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

/**
 * The new email is only stored as pending: it is applied when the user clicks
 * on the link sent to this new address.
 *
 * @see ChangeLoginAction
 * @see ConfirmChangeLoginCommandHandler
 */
#[AsMessageHandler]
final class ChangeLoginCommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
        private readonly AppMailer $appMailer,
    ) {
    }

    public function __invoke(ChangeLoginCommand $message): void
    {
        $user = $this->userRepository->find($message->id);
        Assert::isInstanceOf($user, User::class);
        $this->userManager->requestChangeLogin($user, $message->email);
        $this->userManager->save($user, true);
        $this->appMailer->send(ChangeLoginEmail::class, compact('user'));
    }
}
