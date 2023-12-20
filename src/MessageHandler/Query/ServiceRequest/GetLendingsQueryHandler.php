<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\ServiceRequest;

use App\Message\Query\User\ServiceRequest\GetLendingsQuery;
use App\Repository\ServiceRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetLendingsQueryHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ServiceRequestRepository $serviceRequestRepository,
    ) {
    }

    public function __invoke(GetLendingsQuery $message): Query
    {
        $user = $this->userRepository->get($message->userId);

        return $this->serviceRequestRepository->getLendings($user, $message->products);
    }
}
