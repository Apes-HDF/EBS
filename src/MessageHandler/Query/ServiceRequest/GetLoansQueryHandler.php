<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\ServiceRequest;

use App\Message\Query\User\ServiceRequest\GetLoansQuery;
use App\Repository\ServiceRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetLoansQueryHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ServiceRequestRepository $serviceRequestRepository,
    ) {
    }

    public function __invoke(GetLoansQuery $message): Query
    {
        $user = $this->userRepository->get($message->userId);

        return $this->serviceRequestRepository->getLoans($user, $message->products);
    }
}
