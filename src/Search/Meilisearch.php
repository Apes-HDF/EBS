<?php

declare(strict_types=1);

namespace App\Search;

use App\Controller\Product\ProductController;
use App\Dto\Product\Search;
use App\Entity\Address;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\Product\ProductType;
use App\Enum\Product\ProductVisibility;
use App\Repository\ProductRepository;
use App\Search\Document\ProductDocument;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Search\SearchResult;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * Simple service to index and retrieve results from Meilisearch. Class is not final
 * so we can use the lazy option.
 */
#[Autoconfigure(lazy: true)]
class Meilisearch
{
    final public const PRODUCTS_INDEX = 'products';
    final public const PRODUCTS_SWAP_INDEX = self::PRODUCTS_INDEX.'_swap';

    final public const PRIMARY_KEY = 'id';

    final public const SEARCHABLE_ATTRIBUTES = [
        'name',
        'categories',
        'description',
    ];

    final public const FILTRABLE_ATTRIBUTES = [
        'ownerId',
        'type',
        'visibility',
        'categoriesIds',
        'groupsIds',
        '_geo',
    ];

    final public const SORTABLE_ATTRIBUTES = [
        'name',
        '_geo',
    ];

    /**
     * Maximum time to wait for Meilisearch tasks when (re)building the index. The
     * default timeout of the client (5s) is too short for a full indexation.
     */
    final public const TASK_TIMEOUT_MS = 300_000;

    /**
     * Main Meilisearch client.
     */
    private Client $client;

    /**
     * Main search index (locally cached).
     */
    private ?Indexes $index = null;

    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly ProductRepository $productRepository,
        #[Autowire('%meilisearchUrl%')]
        private readonly string $meilisearchUrl,
        #[Autowire('%meilisearchApiKey%')]
        private readonly string $meilisearchApiKey,
    ) {
        $this->client = new Client($this->meilisearchUrl, $this->meilisearchApiKey);
    }

    /**
     * For direct access to main Meili client.
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    public function getIndex(): Indexes
    {
        if ($this->index !== null) {
            return $this->index;
        }

        $this->index = $this->client->index(self::PRODUCTS_INDEX);

        return $this->index;
    }

    public function getSwapIndex(): Indexes
    {
        return $this->client->index(self::PRODUCTS_SWAP_INDEX);
    }

    /**
     * Creates the index if it doesn't exist yet. Swapping indexes requires both
     * indexes to exist: on an empty Meilisearch instance the swap would fail.
     */
    public function ensureIndex(string $uid): void
    {
        $task = $this->client->createIndex($uid, ['primaryKey' => self::PRIMARY_KEY]);
        $this->waitForTasks([$task['taskUid']], ['index_already_exists']);
    }

    /**
     * Apply all settings at once.
     *
     * @return int the uid of the Meilisearch task
     */
    public function setSettings(?Indexes $index = null): int
    {
        $index = $index ?? $this->getIndex();
        $task = $index->updateSettings([
            'searchableAttributes' => self::SEARCHABLE_ATTRIBUTES,
            'filterableAttributes' => self::FILTRABLE_ATTRIBUTES,
            'sortableAttributes' => self::SORTABLE_ATTRIBUTES,
        ]);

        return $task['taskUid'];
    }

    /**
     * Meilisearch tasks are asynchronous: an error is not raised when the task is
     * created but when it is processed. Waits for the tasks and throws if one of
     * them did not succeed, except for the given expected error codes.
     *
     * @param array<int>    $taskUids
     * @param array<string> $allowedErrorCodes
     */
    public function waitForTasks(array $taskUids, array $allowedErrorCodes = []): void
    {
        $tasks = $this->client->waitForTasks($taskUids, self::TASK_TIMEOUT_MS);
        foreach ($tasks as $task) {
            $errorCode = $task['error']['code'] ?? null;
            if ($task['status'] !== 'succeeded' && !\in_array($errorCode, $allowedErrorCodes, true)) {
                throw new \RuntimeException(\sprintf('Meilisearch task %d (%s) failed with status "%s": %s', $task['uid'], $task['type'], $task['status'], $task['error']['message'] ?? 'no error message'));
            }
        }
    }

    /**
     * Allows to have full control about the normalization. But with tne 1.1 version
     * of Meilisearch, we should be able to pass the document DTO as it is and let
     * Meilisearch handle the normalization process.
     *
     * @return array<string, mixed>
     *
     * @throws ExceptionInterface
     */
    public function normalizeProduct(Product $product): array
    {
        $productDocument = ProductDocument::fromProduct($product);
        /** @var array<string, mixed> $normalized */
        $normalized = $this->normalizer->normalize($productDocument, 'array');

        return $normalized;
    }

    public function deleteProduct(Product $product, ?Indexes $index = null): void
    {
        $index = $index ?? $this->getIndex();
        $index->deleteDocument((string) $product->getId());
    }

    /**
     * @throws ExceptionInterface
     */
    public function indexProduct(Product $product, ?Indexes $index = null): void
    {
        $index = $index ?? $this->getIndex();
        $index->addDocuments([$this->normalizeProduct($product)], self::PRIMARY_KEY);
    }

    /**
     * @param array<Product> $products
     *
     * @return int the uid of the Meilisearch task
     *
     * @throws ExceptionInterface
     */
    public function indexProducts(array $products, ?Indexes $index = null): int
    {
        $index = $index ?? $this->getIndex();
        $documents = array_map(fn (Product $product) => $this->normalizeProduct($product), $products);
        $task = $index->addDocuments($documents, self::PRIMARY_KEY);

        return $task['taskUid'];
    }

    /**
     * Swap indexes to avoid downtime.
     *
     * @return int the uid of the Meilisearch task
     */
    public function swapIndexes(): int
    {
        $task = $this->getClient()->swapIndexes([[self::PRODUCTS_INDEX, self::PRODUCTS_SWAP_INDEX]]);

        return $task['taskUid'];
    }

    public function searchObjects(Search $searchDto): SearchResult
    {
        return $this->search($searchDto, ProductType::OBJECT);
    }

    public function searchServices(Search $searchDto): SearchResult
    {
        return $this->search($searchDto, ProductType::SERVICE);
    }

    /**
     * Search with a main query and various filtery.
     */
    public function search(Search $searchDto, ?ProductType $productType = null): SearchResult
    {
        $searchParams = [];
        $searchParams = $this->withFilters($searchParams, $searchDto, $productType);
        $searchParams = $this->withSort($searchParams, $searchDto);

        // pagination settings
        $searchParams['hitsPerPage'] = ProductController::MAX_ELEMENT_BY_PAGE;
        $searchParams['page'] = $searchDto->page;

        // option to transform hits to products while keeping the relevance order
        $options = ['transformHits' => $this->transformHits(...)];

        return $this->getIndex()->search($searchDto->q, $searchParams, $options);
    }

    /**
     * Apply all search filters.
     *
     * @param array<string, mixed> $searchParams
     *
     * @return array<string, mixed>
     */
    private function withFilters(array $searchParams, Search $searchDto, ?ProductType $productType = null): array
    {
        $filters = [];

        // if the user is NOT logged then he can only view public products
        // if the user is logged he will also view the products belonging to its groups
        $visibilityFilter = [];
        $visibilityFilter[] = 'visibility = '.ProductVisibility::PUBLIC->value;
        if ($searchDto->isLogged()) {
            Assert::isInstanceOf($searchDto->user, User::class);
            $userGroupsIds = $searchDto->user->getUserGroupsIds();
            $visibilityFilter[] = 'groupsIds IN [ '.implode(', ', $userGroupsIds).' ]';
        }
        $filters[] = '( '.implode(' OR ', $visibilityFilter).' )';

        // product type as a filter
        if ($productType !== null) {
            $filters[] = 'type = '.$productType->value;
        }

        // category filter
        if ($searchDto->category !== null) {
            $filters[] = 'categoriesIds = '.$searchDto->category->getId();
        }

        // place filter
        if ($searchDto->place !== null) {
            $filters[] = \sprintf('ownerId = %s', $searchDto->place->getId());
        }

        // geo filter
        if ($searchDto->hasProximity()) {
            Assert::isInstanceOf($searchDto->city, Address::class);
            $filters[] = \sprintf('_geoRadius(%s, %s, %d)',
                $searchDto->city->getLatitude(),
                $searchDto->city->getLongitude(),
                (int) $searchDto->distance * 1000 // the distance is in meters, not kilometers
            );
        }

        // Filters are cumulative
        $searchParams['filter'] = implode(' AND ', $filters);

        return $searchParams;
    }

    /**
     * Apply sort by name or proximity.
     *
     * @param array<string, mixed> $searchParams
     *
     * @return array<string, mixed>
     */
    private function withSort(array $searchParams, Search $searchDto): array
    {
        // the proximity search has the priority to sort results
        if ($searchDto->hasProximity()) {
            Assert::isInstanceOf($searchDto->city, Address::class);
            $searchParams['sort'] = [\sprintf('_geoPoint(%s, %s):asc',
                $searchDto->city->getLatitude(),
                $searchDto->city->getLongitude()),
            ];
        }

        // default sort: if no query is specified and not proximity filter then sort by name
        if (!$searchDto->hasQuery() && !$searchDto->hasProximity()) {
            $searchParams['sort'] = ['name:asc'];
        }

        return $searchParams;
    }

    /**
     * Transform the hits to an array of product. If a product is not found is it
     * simply removed from the results.
     *
     * @param array<int, array<string, mixed>> $hits
     *
     * @return array<Product>
     */
    private function transformHits(array $hits): array
    {
        $products = array_map($this->getProduct(...), $hits);

        return array_filter($products);
    }

    /**
     * @param array<string, mixed> $hit
     */
    private function getProduct(array $hit): ?Product
    {
        $product = $this->productRepository->find($hit['id'] ?? ''); // don't use null as it raises a doctrine exception
        if ($product === null) {
            return null;
        }
        if ($product->getOwner()->isInVacation()) {
            return null;
        }

        // enrich with the distance to the geoPoint if it is available
        if (\array_key_exists('_geoDistance', $hit)) {
            $product->setGeoDistance(\is_int($hit['_geoDistance']) ? $hit['_geoDistance'] : null);
        }

        return $product;
    }
}
