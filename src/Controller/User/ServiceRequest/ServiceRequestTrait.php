<?php

declare(strict_types=1);

namespace App\Controller\User\ServiceRequest;

use App\Entity\ServiceRequest;
use App\Message\Query\User\ServiceRequest\GetServiceRequestByIdQuery;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

/**
 * Service request helpers.
 */
trait ServiceRequestTrait
{
    /**
     * Get a service request we own.
     */
    public function getMyServiceRequest(string $id): ServiceRequest
    {
        try {
            /** @var ServiceRequest $serviceRequest */
            $serviceRequest = $this->queryBus->query(new GetServiceRequestByIdQuery(Uuid::fromString($id)));
        } catch (HandlerFailedException $e) {
            /** @var \Exception $previous */
            $previous = $e->getPrevious();
            throw match (\get_class($previous)) {
                AccessDeniedException::class => $this->createAccessDeniedException($previous->getMessage()),
                default => $this->createNotFoundException($previous->getMessage()),
            };
        }

        return $serviceRequest;
    }
}
