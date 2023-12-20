<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Security;

use App\Doctrine\Manager\UserManager;
use App\Helper\StringHelper;
use App\Mailer\AppMailer;
use App\Mailer\Email\Security\LostPasswordEmail;
use App\Message\Command\Security\LostPasswordCommand;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
/**
 * Validation is done by the LostPasswordFormType.
 *
 * @see LostPasswordAction
 */
final class LostPasswordCommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly AppMailer $appMailer,
        private readonly UserRepository $userRepository,
        private readonly StringHelper $stringHelper,
    ) {
    }

    public function __invoke(LostPasswordCommand $message): void
    {
        Assert::stringNotEmpty($message->email);
        $email = $this->stringHelper->normalizeEmail($message->email);
        $user = $this->userRepository->findOneByEmail($email);

        // we don't tell the user the email was not found for security
        if ($user === null) {
            return;
        }

        $this->userManager->refreshLostPasswordToken($user);
        $this->userManager->save($user, true);
        $this->appMailer->send(LostPasswordEmail::class, compact('user'));
    }
}
