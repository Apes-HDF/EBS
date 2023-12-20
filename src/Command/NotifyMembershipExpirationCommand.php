<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\UserGroup;
use App\Mailer\AppMailer;
use App\Mailer\Email\Command\EndMembershipEmail;
use App\Mailer\Email\Command\NotifyMembershipExpirationEmail;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Repository\UserGroupRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @see NotifyMembershipExpirationCommandTest
 */
#[AsCommand(
    name: self::CMD,
    description: self::DESCRIPTION,
)]
final class NotifyMembershipExpirationCommand extends Command
{
    use CommandTrait;
    use SmsNotifierTrait;

    public const CMD = 'app:notify-membership-expiration';
    public const DESCRIPTION = 'Notify expiring membership.';

    public function __construct(
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
        $this->addArgument('days', InputArgument::REQUIRED, 'Number of days from tomorrow (1 = notifiy members expiring tomorrow)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(self::DESCRIPTION.' ('.$this->environment.' env)');
        $this->memoryReport($io);

        /** @var string $days */
        $days = $input->getArgument('days');
        $days = max(1, (int) $days);

        $io->section(sprintf('Getting membership expiring in %d days...', $days));
        $query = $this->userGroupRepository->getExpiring($days);
        $io->section('Sending notificaitons...');
        $count = 0;
        foreach ($query->toIterable() as $userGroup) {
            /** @var UserGroup $userGroup */
            $user = $userGroup->getUser();
            $group = $userGroup->getGroup();
            $io->comment(sprintf('  > notifying membership for %s of %s/%s (%s) (%s)',
                $group->getName(),
                $user->getDisplayName(),
                $userGroup->getEndAt()?->format('Y-m-d'),
                $userGroup->getMembership()->value,
                $user->getId()
            ));

            $this->appMailer->send(NotifyMembershipExpirationEmail::class, compact('user', 'group', 'days'));
            $this->sendSms($user, EndMembershipEmail::class, [
                '%group%' => $group->getName(),
                '%days' => $days,
            ]);
            ++$count;
        }

        $io->note(sprintf(' > %d notification(s) sent.', $count));

        $this->memoryReport($io);
        $this->done($io);

        return Command::SUCCESS;
    }
}
