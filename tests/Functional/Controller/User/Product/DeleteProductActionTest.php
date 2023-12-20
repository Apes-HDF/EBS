<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Product;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see DeleteProductAction
 */
final class DeleteProductActionTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const EDIT_ROUTE = '/fr/mon-compte/objets/'.TestReference::OBJECT_LOIC_2.'/editer';

    private const DELETE_ROUTE = '/en/my-account/objects/'.TestReference::OBJECT_LOIC_2.'/delete';
    private const DELETE_ROUTE_404 = '/en/my-account/objects/'.TestReference::UUID_404.'/delete';

    private const FLASH_SUCCESS = 'app.controller.user.product.delete_product_action.flash.success';

    /**
     * Unknown product.
     */
    public function testNotFoundException(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::DELETE_ROUTE_404);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Access denied to other users.
     */
    public function testAccessDeniedException(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::DELETE_ROUTE);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Nominal case (owner).
     */
    public function testDeleteProductSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', self::EDIT_ROUTE);
        self::assertResponseIsSuccessful();

        $client->request('GET', self::DELETE_ROUTE);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS);
    }
}
