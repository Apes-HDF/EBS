<?php

declare(strict_types=1);

namespace App\Doctrine\Manager;

use App\Entity\ProductAvailability;
use App\Entity\ServiceRequest;
use App\Enum\Product\ProductAvailabilityMode;
use App\Enum\Product\ProductAvailabilityType;
use App\Repository\ProductAvailabilityRepository;

final class ProductAvailabilityManager
{
    public function __construct(
        private readonly ProductAvailabilityRepository $productAvailabilityRepository,
    ) {
    }

    public function save(ProductAvailability $entity, bool $flush = false): void
    {
        $this->productAvailabilityRepository->save($entity, $flush);
    }

    /**
     * Create the unavailability of a product for a given service request.
     */
    public function createFromServiceRequest(ServiceRequest $serviceRequest, \DateTimeImmutable $startAt, \DateTimeImmutable $endAt): ProductAvailability
    {
        return (new ProductAvailability())
            ->setMode(ProductAvailabilityMode::UNAVAILABLE)
            ->setType(ProductAvailabilityType::SERVICE_REQUEST)
            ->setServiceRequest($serviceRequest)
            ->setProduct($serviceRequest->getProduct())
            ->setStartAt($startAt)
            ->setEndAt($endAt);
    }
}
