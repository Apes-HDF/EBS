<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Meilisearch\Search\SearchResult;

/**
 * @property PaginatorInterface $paginator
 */
trait PaginationTrait
{
    /**
     * Generate a pagination object from a Meilisearch search result object.
     *
     * @see SearchResultSubscriber
     *
     * @implements PaginationInterface<int,Product>
     *
     * @return PaginationInterface<int,mixed>
     */
    private function paginate(SearchResult $searchResult): PaginationInterface
    {
        return $this->paginator->paginate(
            $searchResult,
            (int) $searchResult->getPage(),
            (int) $searchResult->getHitsPerPage()
        );
    }
}
