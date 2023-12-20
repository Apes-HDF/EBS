<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Product;

use App\Controller\Product\ProductController;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see ProductController
 */
final class ProductControllerTest extends WebTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;
    use KernelTrait;

    private const ROUTE_LIST = '/fr/produits';
    private const ROUTE_SHOW = self::ROUTE_LIST.'/velo-fuji-jari-2-5/1ed7a2a8-0a77-6dbc-a34f-f3a729006754';

    public function testListSuccess(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_LIST);
        self::assertResponseIsSuccessful();
    }

    public function testListPaginationSuccess(): void
    {
        $client = self::createClient();

        // 2nd valid page
        $client->request('GET', self::ROUTE_LIST.'?page=2');
        self::assertResponseIsSuccessful();

        // no error 500 on invalid page
        $client->request('GET', self::ROUTE_LIST.'?page=5464646546542');
        self::assertResponseIsSuccessful();

        // no error 500 on non numeric page
        $client->request('GET', self::ROUTE_LIST.'?page=--fobarr');
        self::assertResponseIsSuccessful();
    }

    public function testShowNotFoundFailure(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_LIST.'/my-slug/'.TestReference::UUID_404);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testShowSuccess(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_SHOW);
        self::assertResponseIsSuccessful();
    }

    public function testShowLoggedCanBorrowSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('GET', self::ROUTE_SHOW);
        self::assertResponseIsSuccessful();
    }

    public function testShowLoggedCannotBorrowSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', self::ROUTE_SHOW);
        self::assertResponseIsSuccessful();
    }
}
