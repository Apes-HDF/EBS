<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Payment;

use App\Doctrine\Manager\UserManager;
use App\Entity\UserGroup;
use App\Enum\Group\UserMembership;
use App\Message\Command\Payment\DoneCommand;
use App\Repository\GroupOfferRepository;
use App\Repository\UserRepository;
use Carbon\CarbonImmutable;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DoneCommandHandler
{
    public function __construct(
        private readonly Payum $payum,
        private readonly GroupOfferRepository $groupOfferRepository,
        private readonly UserRepository $userRepository,
        private readonly UserManager $userManager,
    ) {
    }

    public function __invoke(DoneCommand $message): GetHumanStatus
    {
        $groupOffer = $this->groupOfferRepository->get($message->groupOfferId);
        $group = $groupOffer->getGroup();
        $user = $this->userRepository->get($message->userId);

        $gateway = $this->payum->getGateway($message->paymentToken->getGatewayName());
        $status = new GetHumanStatus($message->paymentToken);
        $gateway->execute($status);

        // /** @var Payment $payment */
        // $payment = $status->getFirstModel();

        // Not captured
        if (!$status->isCaptured()) {
            return $status;
        }

        // user has an invitation for this group
        if ($user->hasLink($group)) {
            /** @var UserGroup $userGroup */
            $userGroup = $user->getGroupMembership($group);
        } else {
            $userGroup = (new UserGroup())
                ->setUser($user)
                ->setGroup($groupOffer->getGroup());
        }

        // promote to member
        $userGroup
            ->setMembership(UserMembership::MEMBER)
            ->setStartAt(CarbonImmutable::today())
            ->setPayedAt(CarbonImmutable::now())
        ;

        // set the end date for recurring offers
        $offerType = $groupOffer->getType();
        if ($offerType->isRecurring()) {
            $userGroup->setEndAt(new CarbonImmutable($offerType->getEndAtInterval()));
        }
        $user->addUserGroup($userGroup);
        $this->userManager->save($user, true);

        // payment was captured and membership is saved so invalidate the token
        $this->payum->getHttpRequestVerifier()->invalidate($message->paymentToken);

        return $status;
    }
}
