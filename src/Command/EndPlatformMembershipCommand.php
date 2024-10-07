<?php

declare(strict_types=1);

namespace App\Command;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Enum\OfferType;
use App\Mailer\AppMailer;
use App\Mailer\Email\Command\EndPlatformMembershipMail;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Repository\ConfigurationRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: self::CMD, description: self::DESCRIPTION)]
class EndPlatformMembershipCommand extends Command
{
    use CommandTrait;
    use SmsNotifierTrait;

    public const CMD = 'app:end-platform-membership';
    public const DESCRIPTION = 'Check overdue platform membership and set user as unpaid';

    public function __construct(
        private readonly UserRepository $userRepository,
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
        private readonly AppMailer $appMailer,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly TranslatorInterface $translator,
        private readonly SmsNotifier $notifier,
        #[Autowire('%brand%')]
        private readonly string $brand,
        private readonly UserManager $userManager,
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
        $configuration = $this->configurationRepository->getInstanceConfigurationOrCreate();
        if (!$configuration->getPaidMembership()) {
            return Command::SUCCESS;
        }
        $platform = $configuration->getPlatformName();
        $io->title(self::DESCRIPTION.' ('.$this->environment.' env)');
        $this->memoryReport($io);

        $io->section('Getting concerned membership...');
        $query = $this->userRepository->getExpiredMembership();

        $io->section('Processing user updates...');
        $count = 0;
        /** @var User $user */
        foreach ($query->toIterable() as $user) {
            if ($user->getPlatformOffer()?->getType() === OfferType::ONESHOT) {
                continue;
            }

            $io->comment(\sprintf('  > ending platform membership expiration for user %s (%s)',
                $user->getDisplayName(),
                $user->getEmail(),
            ));

            // save it here for the mail before setting it back to null
            $endAt = $user->getEndAt();

            $user->setMembershipPaid(false)
                ->setEndAt(null)
                ->setPayedAt(null)
                ->setStartAt(null)
                ->setPlatformOffer(null);

            $this->userManager->save($user, true);

            $this->appMailer->send(EndPlatformMembershipMail::class, compact('user', 'platform', 'endAt'));
            $this->sendSms($user, EndPlatformMembershipMail::class, ['%platform%' => $platform]);
            ++$count;
        }

        $io->note(\sprintf(' > %d update(s) done.', $count));
        $this->memoryReport($io);
        $this->done($io);

        return Command::SUCCESS;
    }
}
