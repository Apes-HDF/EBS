<?php

declare(strict_types=1);

namespace App\Tests\Integration\Search;

use App\Dto\Product\Search;
use App\Entity\Address;
use App\Entity\Product;
use App\Entity\User;
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
        self::assertNotEmpty($results);
    }

    /**
     * The distance to the product of a user is computed from the blurred location
     * of its owner, not from the exact address.
     */
    public function testProximitySearchDistanceUsesTheBlurredOwnerLocation(): void
    {
        /** @var Meilisearch $meilisearch */
        $meilisearch = self::getContainer()->get(Meilisearch::class);
        $owner = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);
        $product = $this->getProductRepository()->get(TestReference::OBJECT_LOIC_1);

        $index = $meilisearch->getIndex();
        $task = $index->addDocuments([$meilisearch->normalizeProduct($product)], Meilisearch::PRIMARY_KEY);
        $index->waitForTask($task['taskUid']);

        // reference point chosen by the visitor (Lille)
        $city = (new Address())
            ->setLocality('Lille')
            ->setLatitude('50.6365654')
            ->setLongitude('3.0635282');

        $distance = $this->getDistance($meilisearch, $city, $owner, (string) $product->getId());

        // distance to the grid node (50.62, 3.08), not to the address (50.6241657, 3.0809541)
        self::assertEqualsWithDelta(self::haversine(50.6365654, 3.0635282, 50.62, 3.08), $distance, 0.01);
        self::assertGreaterThan(0.1, abs($distance - self::haversine(50.6365654, 3.0635282, 50.6241657, 3.0809541)));
    }

    private function getDistance(Meilisearch $meilisearch, Address $city, User $owner, string $productId): float
    {
        $searchDto = new Search('');
        $searchDto->city = $city;
        $searchDto->distance = 20;
        $searchDto->place = $owner;
        $products = $meilisearch->search($searchDto)->getHits();
        foreach ($products as $product) {
            self::assertInstanceOf(Product::class, $product);
            if ((string) $product->getId() === $productId) {
                self::assertNotNull($product->getGeoDistance());

                return $product->getGeoDistance();
            }
        }

        self::fail('Product not found in the proximity search.');
    }

    /**
     * Distance in km between two points.
     */
    private static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371.0 * 2 * asin(sqrt($a));
    }
}
