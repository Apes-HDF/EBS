<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Payment;

use App\Doctrine\Manager\UserManager;
use App\Mailer\AppMailer;
use App\Mailer\Email\Payment\PlatformMembershipPaidMail;
use App\Message\Command\Payment\PlatformMembershipPaidCommand;
use App\Repository\ConfigurationRepository;
use App\Repository\PlatformOfferRepository;
use App\Repository\UserRepository;
use Carbon\CarbonImmutable;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PlatformMembershipPaidCommandHandler
{
    public function __construct(
        private readonly PlatformOfferRepository $platformOfferRepository,
        private readonly UserRepository $userRepository,
        private readonly UserManager $userManager,
        private readonly Payum $payum,
        private readonly AppMailer $mailer,
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    public function __invoke(PlatformMembershipPaidCommand $message): GetHumanStatus
    {
        $platformOffer = $this->platformOfferRepository->get($message->platformOfferId);
        $user = $this->userRepository->get($message->userId);

        $gateway = $this->payum->getGateway($message->paymentToken->getGatewayName());
        $status = new GetHumanStatus($message->paymentToken);
        $gateway->execute($status);

        // Not captured
        if (!$status->isCaptured()) {
            return $status;
        }

        $user
            ->setMembershipPaid(true)
            ->setStartAt(CarbonImmutable::today())
            ->setPayedAt(CarbonImmutable::now())
            ->setPlatformOffer($platformOffer)
        ;
        if (($offerType = $platformOffer->getType())->isRecurring()) {
            $user->setEndAt(new CarbonImmutable($offerType->getEndAtInterval()));
        }

        $this->userManager->save($user, true);

        // payment was captured and membership is saved so invalidate the token
        $this->payum->getHttpRequestVerifier()->invalidate($message->paymentToken);

        // send confirmation email
        $configuration = $this->configurationRepository->getInstanceConfigurationOrCreate();
        $platform = $configuration->getPlatformName();
        $this->mailer->send(PlatformMembershipPaidMail::class, [
            'platform' => $platform,
            'user' => $user,
        ]);

        return $status;
    }
}
