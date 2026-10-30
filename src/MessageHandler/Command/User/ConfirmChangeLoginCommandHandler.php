<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Exception\UserPendingEmailAlreadyUsedException;
use App\Exception\UserPendingEmailTokenExpiredException;
use App\Message\Command\User\ConfirmChangeLoginCommand;
use App\Repository\UserRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

/**
 * @see ConfirmChangeLoginAction
 */
#[AsMessageHandler]
final class ConfirmChangeLoginCommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(ConfirmChangeLoginCommand $message): void
    {
        $user = $this->userRepository->find($message->id);
        Assert::isInstanceOf($user, User::class);

        // the token must be the one of the connected user
        $token = $user->getPendingEmailToken();
        $pendingEmail = $user->getPendingEmail();
        if ($token === null || $pendingEmail === null || !hash_equals($token, $message->token)) {
            throw new UserNotFoundException($message->token);
        }

        if ($user->isPendingEmailTokenExpired($this->clock->now())) {
            throw new UserPendingEmailTokenExpiredException();
        }

        // the email may have been taken by another account (confirmed or not) since the request
        $existingUser = $this->userRepository->findOneByEmail($pendingEmail);
        if ($existingUser !== null && !$existingUser->getId()->equals($user->getId())) {
            throw new UserPendingEmailAlreadyUsedException();
        }

        $this->userManager->confirmChangeLogin($user);
        $this->userManager->save($user, true);
    }
}
