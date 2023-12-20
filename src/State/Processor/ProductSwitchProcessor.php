<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Webmozart\Assert\Assert;

/**
 * Change the status of a product from active to paused or the opposite.
 */
final class ProductSwitchProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * @param array<mixed> $uriVariables
     * @param array<mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Product
    {
        Assert::isInstanceOf($data, Product::class);
        $data->switchStatus();
        $this->productRepository->save($data, true);

        return $data;
    }
}
