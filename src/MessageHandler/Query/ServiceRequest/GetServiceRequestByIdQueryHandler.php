<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\ServiceRequest;

use App\Entity\ServiceRequest;
use App\Exception\ServiceRequest\ServiceRequestNotFoundException;
use App\Message\Query\User\ServiceRequest\GetServiceRequestByIdQuery;
use App\Repository\ServiceRequestRepository;
use App\Security\Voter\ServiceRequest\ServiceRequestVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsMessageHandler]
final class GetServiceRequestByIdQueryHandler
{
    public function __construct(
        private readonly ServiceRequestRepository $serviceRequestRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * Retrieve the service request from the database.
     */
    public function __invoke(GetServiceRequestByIdQuery $message): ServiceRequest
    {
        $serviceRequest = $this->serviceRequestRepository->find($message->id);
        if ($serviceRequest === null) {
            throw new ServiceRequestNotFoundException($message->id);
        }

        if (!$this->security->isGranted(ServiceRequestVoter::VIEW, $serviceRequest)) {
            throw new AccessDeniedException(\sprintf('Access to service request "%s" denied (not owner or recipient).', $message->id));
        }

        return $serviceRequest;
    }
}
