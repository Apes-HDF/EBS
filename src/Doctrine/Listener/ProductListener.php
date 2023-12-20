<?php

declare(strict_types=1);

namespace App\Doctrine\Listener;

use App\Entity\Product;
use App\Search\Meilisearch;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Synchronize products in Meilisearch. The logic is in the isIndexable() functions.
 */
final class ProductListener
{
    public function __construct(
        private readonly Meilisearch $meilisearch,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function postUpdate(Product $product): void
    {
        if ($product->isIndexable()) {
            $this->meilisearch->indexProduct($product);
        } else {
            // remove from index
            $this->meilisearch->deleteProduct($product);
        }
    }

    public function postPersist(Product $product): void
    {
        $this->meilisearch->indexProduct($product);
    }

    public function preRemove(Product $product): void
    {
        $this->meilisearch->deleteProduct($product);
    }
}
