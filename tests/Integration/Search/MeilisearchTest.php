<?php

declare(strict_types=1);

namespace App\Tests\Integration\Search;

use App\Dto\Product\Search;
use App\Search\Meilisearch;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Meilisearch\Exceptions\ApiException;
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
        self::assertNotEmpty($results);
    }

    public function testWaitForTasksThrowsOnFailedTask(): void
    {
        /** @var Meilisearch $meilisearch */
        $meilisearch = self::getContainer()->get(Meilisearch::class);
        // a task on a missing index fails when it is processed, not when it is created
        $task = $meilisearch->getClient()->index('missing_index')->deleteAllDocuments();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Meilisearch task \d+ \(documentDeletion\) failed with status "failed"/');
        $meilisearch->waitForTasks([$task['taskUid']]);
    }

    public function testSyncProducts(): void
    {
        /** @var Meilisearch $meilisearch */
        $meilisearch = self::getContainer()->get(Meilisearch::class);
        $product = $this->getProductRepository()->get(TestReference::OBJECT_LOIC_1);
        $id = (string) $product->getId();

        $meilisearch->waitForTasks($meilisearch->syncProducts([], [$product]));
        self::assertFalse($this->isInIndex($meilisearch, $id));

        $meilisearch->waitForTasks($meilisearch->syncProducts([$product], []));
        self::assertTrue($this->isInIndex($meilisearch, $id));
    }

    public function testAssertDocumentCountThrowsOnMismatch(): void
    {
        /** @var Meilisearch $meilisearch */
        $meilisearch = self::getContainer()->get(Meilisearch::class);
        $index = $meilisearch->getIndex();
        $count = $index->stats()['numberOfDocuments'];
        $meilisearch->assertDocumentCount($index, $count);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('The "products" index contains %d document(s) instead of %d.', $count, $count + 1));
        $meilisearch->assertDocumentCount($index, $count + 1);
    }

    private function isInIndex(Meilisearch $meilisearch, string $id): bool
    {
        try {
            $meilisearch->getIndex()->getDocument($id);
        } catch (ApiException $e) {
            self::assertSame(404, $e->httpStatus);

            return false;
        }

        return true;
    }
}
