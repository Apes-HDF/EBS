<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\Product;

use App\Entity\Product;
use App\Entity\ProductAvailability;
use App\Message\Command\User\Product\CreateProductUnavailabilityCommand;
use App\Repository\ProductAvailabilityRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final class CreateProductAvailabilityHandler
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductAvailabilityRepository $productAvailabilityRepository,
    ) {
    }

    public function __invoke(CreateProductUnavailabilityCommand $message): ProductAvailability
    {
        $product = $this->productRepository->find($message->productId);
        Assert::isInstanceOf($product, Product::class);

        $newProductAvailability = ProductAvailability::productAvailabilityCreationByOwner($product, $message->startAt, $message->endAt);
        $this->productAvailabilityRepository->save($newProductAvailability, true);

        return $newProductAvailability;
    }
}
