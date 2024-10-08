<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Enum\OfferType;
use App\Mailer\AppMailer;
use App\Mailer\Email\Command\NotifyPlatformMembershipExpirationMail;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Repository\ConfigurationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: self::CMD, description: self::DESCRIPTION)]
class NotifyPlatformMembershipExpirationCommand extends Command
{
    use CommandTrait;
    use SmsNotifierTrait;

    public const CMD = 'app:notify-platform-membership-expiration';
    public const DESCRIPTION = 'Notify expiring platform membership.';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator,
        private readonly AppMailer $appMailer,
        private readonly SmsNotifier $notifier,
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
        #[Autowire('%brand%')]
        private readonly string $brand,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->configureCommand(self::DESCRIPTION);
        $this->addArgument('days', InputArgument::REQUIRED, 'Number of days from tomorrow (1 = notify members expiring tomorrow)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $configuration = $this->configurationRepository->getInstanceConfigurationOrCreate();
        if (!$configuration->getPaidMembership()) {
            return Command::SUCCESS;
        }
        $platform = $configuration->getPlatformName();
        $io->title(self::DESCRIPTION.' ('.$this->environment.' env)');
        $this->memoryReport($io);

        /** @var string $days */
        $days = $input->getArgument('days');
        $days = max(1, (int) $days);

        $io->section(\sprintf('Getting platform membership expiring in %d days...', $days));
        $query = $this->userRepository->getExpiring($days);
        $io->section('Sending notifications...');
        $count = 0;
        /** @var User $user */
        foreach ($query->toIterable() as $user) {
            if ($user->getPlatformOffer()?->getType() === OfferType::ONESHOT) {
                continue;
            }

            $io->comment(\sprintf('  > notifying platform membership expiration for user %s (%s)',
                $user->getDisplayName(),
                $user->getEmail(),
            ));

            $this->appMailer->send(NotifyPlatformMembershipExpirationMail::class, compact('user', 'days', 'platform'));
            $this->sendSms($user, NotifyPlatformMembershipExpirationMail::class, ['%days%' => $days, '%platform%' => $platform]);
            ++$count;
        }

        $io->note(\sprintf(' > %d notification(s) sent.', $count));

        $this->memoryReport($io);
        $this->done($io);

        return Command::SUCCESS;
    }
}
