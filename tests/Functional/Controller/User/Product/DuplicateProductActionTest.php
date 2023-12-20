<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Product;

use App\Controller\User\Product\DuplicateProductAction;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see DuplicateProductAction
 */
final class DuplicateProductActionTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/objets/';

    private const ROUTE_OK = self::ROUTE.TestReference::OBJECT_LOIC_1.'/dupliquer';
    private const ROUTE_404 = self::ROUTE.TestReference::UUID_404.'/dupliquer';

    private const FLASH_SUCCESS = 'app.controller.user.product.duplicate_product_action.flash.success';

    /**
     * Unknown product.
     */
    public function testNotFoundException(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::ROUTE_404);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Access denied to other users.
     */
    public function testAccessDeniedException(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::ROUTE_OK);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Nominal case (owner).
     */
    public function testFormOwnerSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', self::ROUTE_OK);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS);
    }
}
