<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\User\Account;

use App\Entity\User;
use App\Message\Query\User\Account\GetUserQuery;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetUserQueryHandler
{
    public function __construct(
        public readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(GetUserQuery $message): User
    {
        return $this->userRepository->get($message->id);
    }
}
