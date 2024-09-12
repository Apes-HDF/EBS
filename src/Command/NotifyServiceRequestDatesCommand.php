<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ServiceRequest;
use App\Mailer\AppMailer;
use App\Mailer\Email\Command\NotifyServiceRequestEndEmail;
use App\Mailer\Email\Command\NotifyServiceRequestStartEmail;
use App\Notifier\SmsNotifier;
use App\Notifier\SmsNotifierTrait;
use App\Repository\ServiceRequestRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @see NotifyServiceRequestStartCommandTest
 */
#[AsCommand(
    name: self::CMD,
    description: self::DESCRIPTION,
)]
final class NotifyServiceRequestDatesCommand extends Command
{
    use CommandTrait;
    use SmsNotifierTrait;

    public const CMD = 'app:notify-service-request-dates';
    public const DESCRIPTION = 'Notify owners and recipients before the start and the end of services requests.';

    public function __construct(
        private readonly ServiceRequestRepository $serviceRequestRepository,
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
        $this->addArgument('mode', InputArgument::REQUIRED, 'If the notification is related to the startAt (value = start) date or endAt date (vakue = end) of the service request.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(self::DESCRIPTION.' ('.$this->environment.' env)');
        $this->memoryReport($io);

        /** @var string $mode */
        $mode = $input->getArgument('mode');
        $isStartMode = $mode === 'start';
        $io->note(' > mode : '.$mode);
        $io->newLine();

        $io->section('Getting services request...');
        $query = $isStartMode ?
            $this->serviceRequestRepository->getStartingAtTomorow() :
            $this->serviceRequestRepository->getEndingAtTomorow()
        ;
        $emailClass = $isStartMode ?
            NotifyServiceRequestStartEmail::class :
            NotifyServiceRequestEndEmail::class
        ;

        $io->section('Sending notifications...');
        $count = 0;
        foreach ($query->toIterable() as $serviceRequest) {
            /** @var ServiceRequest $serviceRequest */
            $referenceDate = $isStartMode ?
                $serviceRequest->getStartAt() :
                $serviceRequest->getEndAt()
            ;
            $io->comment(\sprintf('  > notifying owner and recipient for service request %s (%s) starting on %s.',
                $serviceRequest->getId(),
                $serviceRequest->getStatus()->value,
                $referenceDate->format('Y-m-d')
            ));
            $context = [
                'service_request' => $serviceRequest,
                'user' => $serviceRequest->getOwner(),
                '%product%' => $serviceRequest->getProduct()->getName(),
                '%date%' => $referenceDate->format($this->translator->trans('format.date', [], 'date')),
            ];

            $this->appMailer->send($emailClass, $context);
            $this->sendSms($serviceRequest->getOwner(), $emailClass, $context);

            $context['user'] = $serviceRequest->getRecipient();
            $this->appMailer->send($emailClass, $context);
            $this->sendSms($serviceRequest->getRecipient(), $emailClass, $context);

            ++$count;
        }

        $io->note(\sprintf(' > %d notification(s) sent.', $count * 2)); // owner and recipient

        $this->memoryReport($io);
        $this->done($io);

        return Command::SUCCESS;
    }
}
