<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Message\Command\User\ChangeVacationModeCommand;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final class ChangeVacationModeCommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(ChangeVacationModeCommand $message): void
    {
        $user = $this->userRepository->find($message->id);
        Assert::isInstanceOf($user, User::class);
        $user->switchVacationMode($user->getVacationMode());

        $this->userManager->save($user, true);
    }
}
