<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\Product;

use App\Entity\ProductAvailability;
use App\Message\Query\Product\GetProductUnavailabilitiesQuery;
use App\Repository\ProductAvailabilityRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetProductUnavailabilitiesQueryHandler
{
    public function __construct(
        public readonly ProductAvailabilityRepository $productAvailabilityRepository,
        private readonly ProductRepository $productRepository,
    ) {
    }

    /**
     * @return array<ProductAvailability>
     */
    public function __invoke(GetProductUnavailabilitiesQuery $message): array
    {
        $product = $this->productRepository->get($message->id);

        return $this->productAvailabilityRepository->getProductUnavailabilities($product);
    }
}
