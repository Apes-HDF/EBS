<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\UserGroup;
use App\Mailer\AppMailer;
use App\Mailer\Email\Command\EndMembershipEmail;
use App\Message\Command\User\Group\QuitGroupCommand;
use App\MessageBus\CommandBus;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Repository\UserGroupRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @see EndMembershipCommandTest
 */
#[AsCommand(
    name: self::CMD,
    description: self::DESCRIPTION,
)]
final class EndMembershipCommand extends Command
{
    use CommandTrait;
    use SmsNotifierTrait;

    public const CMD = 'app:end-membership';
    public const DESCRIPTION = 'Check overdue membership and remove user from the groups.';

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly UserGroupRepository $userGroupRepository,
        private readonly TranslatorInterface $translator,
        private readonly AppMailer $appMailer,
        private readonly SmsNotifier $notifier,
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
        #[Autowire('%brand%')]
        private readonly string $brand,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->configureCommand(self::DESCRIPTION);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(self::DESCRIPTION.' ('.$this->environment.' env)');
        $this->memoryReport($io);

        $io->section('Getting concerned membership...');
        $query = $this->userGroupRepository->getExpired();

        $io->section('Processing deletions...');
        $count = 0;
        foreach ($query->toIterable() as $userGroup) {
            /** @var UserGroup $userGroup */
            $user = $userGroup->getUser();
            $group = $userGroup->getGroup();
            $io->comment(\sprintf('  > deleting membership for %s of %s (%s) (%s)',
                $group->getName(),
                $user->getDisplayName(),
                $userGroup->getMembership()->value,
                $user->getId()
            ));

            // we could pass the UserGroup instance, but let's use the same command and handler for now
            // As it isn't a user action, this command must put the product in vaction
            // mode to avoid leaving products public as public without the user consent.
            $quitGroupCommand = new QuitGroupCommand($group->getId(), $user->getId(), QuitGroupCommand::VACATION);
            $this->commandBus->dispatch($quitGroupCommand);
            $this->appMailer->send(EndMembershipEmail::class, compact('user', 'group'));
            $this->sendSms($user, EndMembershipEmail::class, [
                '%group%' => $group->getName(),
            ]);
            ++$count;
        }

        $io->note(\sprintf(' > %d deletion(s) done.', $count));
        $this->memoryReport($io);
        $this->done($io);

        return Command::SUCCESS;
    }
}
