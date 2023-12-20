<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Message\Command\User\ChangeLoginCommand;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

/**
 * @see ChangeLoginAction
 */
#[AsMessageHandler]
final class ChangeLoginCommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(ChangeLoginCommand $message): void
    {
        $user = $this->userRepository->find($message->id);
        Assert::isInstanceOf($user, User::class);
        $this->userManager->changeLogin($user, $message->email);
        $this->userManager->save($user, true);
    }
}
