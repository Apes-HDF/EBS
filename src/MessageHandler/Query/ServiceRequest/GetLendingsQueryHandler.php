<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\ServiceRequest;

use App\Entity\ServiceRequest;
use App\Message\Query\User\ServiceRequest\GetLendingsQuery;
use App\Repository\ServiceRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetLendingsQueryHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private ServiceRequestRepository $serviceRequestRepository,
    ) {
    }

    /**
     * @return Query<null, ServiceRequest>
     */
    public function __invoke(GetLendingsQuery $message): Query
    {
        $user = $this->userRepository->get($message->userId);

        return $this->serviceRequestRepository->getLendings($user, $message->products);
    }
}
