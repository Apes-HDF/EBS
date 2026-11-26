<?php

declare(strict_types=1);

namespace App\Command;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Helper\StringHelper;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Sets the password of an account, for example the main admin account created
 * by the production fixtures, which has no usable password.
 *
 * The password is asked interactively (hidden input, a TTY is required), never
 * passed as an argument. It must follow the password rules of the application,
 * with a higher minimum length.
 *
 * @see SetUserPasswordCommandTest
 */
#[AsCommand(
    name: self::CMD,
    description: self::DESCRIPTION,
)]
final class SetUserPasswordCommand extends Command
{
    use CommandTrait;

    public const CMD = 'app:user:set-password';
    public const DESCRIPTION = 'Set the password of an account (asked interactively).';
    public const MIN_LENGTH = 12;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserManager $userManager,
        private readonly StringHelper $stringHelper,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->configureCommand(self::DESCRIPTION);
        $this->addArgument('email', InputArgument::REQUIRED, 'Email of the account');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $email */
        $email = $input->getArgument('email');
        $user = $this->userRepository->findOneBy(['email' => $this->stringHelper->normalizeEmail($email)]);
        if ($user === null) {
            $io->error('No account found for this email.');

            return Command::FAILURE;
        }

        $password = $this->askPassword($io, 'New password ('.self::MIN_LENGTH.' characters minimum)');
        if (mb_strlen($password) < self::MIN_LENGTH) {
            $io->error('The password must be at least '.self::MIN_LENGTH.' characters long.');

            return Command::FAILURE;
        }
        $violations = $this->validator->validatePropertyValue(User::class, 'plainPassword', $password);
        if (\count($violations) > 0) {
            $io->error((string) $violations->get(0)->getMessage());

            return Command::FAILURE;
        }
        if ($password !== $this->askPassword($io, 'Repeat the password')) {
            $io->error('The passwords do not match.');

            return Command::FAILURE;
        }

        $user->setPlainPassword($password);
        $this->userManager->updatePassword($user);
        $user->eraseCredentials();
        $this->userManager->save($user, true);
        $io->success('Password updated.');
        if (!$user->isEnabled() || !$user->isEmailConfirmed()) {
            $io->warning('This account is disabled or its email is not confirmed: it still cannot log in.');
        }

        return Command::SUCCESS;
    }

    /**
     * Hidden input without fallback: fails instead of echoing the password when
     * there is no TTY (docker or kubectl exec without -t).
     */
    private function askPassword(SymfonyStyle $io, string $label): string
    {
        $question = (new Question($label))->setHidden(true)->setHiddenFallback(false);
        $answer = $io->askQuestion($question);

        return \is_string($answer) ? $answer : '';
    }
}
