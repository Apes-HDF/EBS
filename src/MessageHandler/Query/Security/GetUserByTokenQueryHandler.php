<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\Security;

use App\Entity\User;
use App\Exception\UserConfirmationTokenExpiredException;
use App\Exception\UserNotFoundException;
use App\Message\Query\Security\GetUserByTokenQuery;
use App\Repository\UserRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetUserByTokenQueryHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * Try to find the user from the repository and check the validity of the token.
     */
    public function __invoke(GetUserByTokenQuery $message): ?User
    {
        $user = $this->userRepository->findOneByConfirmationToken($message->token);

        if ($user === null) {
            throw new UserNotFoundException($message->token);
        }

        if ($user->isConfirmationTokenExpired($this->clock->now())) {
            throw new UserConfirmationTokenExpiredException($user->getId());
        }

        return $user;
    }
}
