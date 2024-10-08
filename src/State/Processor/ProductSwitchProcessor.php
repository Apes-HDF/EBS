<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product;
use App\Repository\ProductRepository;

/**
 * Change the status of a product from active to paused or the opposite.
 *
 * @implements ProcessorInterface<Product,Product>
 */
final class ProductSwitchProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Product
    {
        $data->switchStatus();
        $this->productRepository->save($data, true);

        return $data;
    }
}
