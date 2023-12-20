<?php

declare(strict_types=1);

namespace App\Tests\Integration\Search;

use App\Dto\Product\Search;
use App\Search\Meilisearch;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MeilisearchTest extends KernelTestCase
{
    use ContainerRepositoryTrait;

    public function testIndexProducts(): void
    {
        /** @var Meilisearch $meilisearch */
        $meilisearch = self::getContainer()->get(Meilisearch::class);
        $object = $this->getProductRepository()->get(TestReference::OBJECT_LOIC_1);
        $service = $this->getProductRepository()->get(TestReference::SERVICE_LOIC_1);
        $meilisearch->indexProducts([$object, $service]);
        $searchDto = new Search('vélo');
        $results = $meilisearch->search($searchDto);
        self::assertNotEmpty($results->getHitsCount());
    }
}
