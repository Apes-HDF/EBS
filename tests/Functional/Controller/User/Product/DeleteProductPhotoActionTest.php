<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Product;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see DeleteProductPhotoAction
 */
final class DeleteProductPhotoActionTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const EDIT_ROUTE = '/fr/mon-compte/objets/'.TestReference::OBJECT_LOIC_1.'/editer';

    private const DELETE_PHOTO_ROUTE = '/en/my-account/objects/'.TestReference::OBJECT_LOIC_1.'/delete-photo/'.TestReference::OBJECT_LOIC_PHOTO_1;
    private const DELETE_PHOTO_ROUTE_404 = '/en/my-account/objects/'.TestReference::UUID_404.'/delete-photo/'.TestReference::OBJECT_LOIC_PHOTO_1;

    private const FLASH_SUCCESS = 'app.controller.user.product.delete_product_photo_action.flash.success';

    /**
     * Unknown product.
     */
    public function testNotFoundException(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::DELETE_PHOTO_ROUTE_404);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Access denied to other users.
     */
    public function testAccessDeniedException(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::DELETE_PHOTO_ROUTE);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Nominal case (owner).
     */
    public function testOwnerSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', self::EDIT_ROUTE);
        self::assertResponseIsSuccessful();

        // @todo: use POST+csrf token
        $client->request('GET', self::DELETE_PHOTO_ROUTE);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS);
    }
}
