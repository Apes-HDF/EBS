<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Product;

use App\Controller\Product\ProductController;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see ProductController
 */
final class ProductSearchControllerTest extends WebTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;
    use KernelTrait;

    private const ROUTE_LIST = '/fr/produits';

    /**
     * Search when using the menu seach bar.
     */
    public function testSearchWithSimpleQuery(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_LIST.'?q=vélo');
        self::assertResponseIsSuccessful();
    }

    /**
     * Advanced search.
     */
    public function testSearchSuccess(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', self::ROUTE_LIST);
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('p_submit')->form();
        $client->submit($form, [
            $form->getName().'[q]' => 'vélo',
            $form->getName().'[category]' => TestReference::CATEGORY_OBJECT_2,
            $form->getName().'[city]' => 'Lille',
            $form->getName().'[distance]' => 5,
        ]);
        self::assertResponseIsSuccessful();
    }

    /**
     * Place.
     */
    public function testSearchPlaceSuccess(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', self::ROUTE_LIST);
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('p_submit')->form();
        $client->submit($form, [
            $form->getName().'[place]' => TestReference::PLACE_APES,
        ]);
        self::assertResponseIsSuccessful();
    }
}
