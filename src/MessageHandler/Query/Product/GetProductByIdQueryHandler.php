<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\Product;

use App\Entity\Product;
use App\Exception\Product\ProductNotFoundException;
use App\Message\Query\Product\GetProductByIdQuery;
use App\Repository\ProductRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetProductByIdQueryHandler
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * Retrieve the product from the database and check access right.
     */
    public function __invoke(GetProductByIdQuery $message): Product
    {
        $product = $this->productRepository->find($message->id);
        if ($product === null) {
            throw new ProductNotFoundException($message->id);
        }

        if ($message->attribute !== null && !$this->security->isGranted($message->attribute, $product)) {
            throw new AccessDeniedHttpException("Access to product {$product->getId()} and attribute $message->attribute denied.");
        }

        return $product;
    }
}
