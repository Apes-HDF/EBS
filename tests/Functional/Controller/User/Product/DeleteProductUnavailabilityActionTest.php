<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Product;

use App\Controller\User\Product\DeleteProductUnavailabilityAction;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see DeleteProductUnavailabilityAction
 */
final class DeleteProductUnavailabilityActionTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE_OK = '/fr/mon-compte/produits/indisponibilite/'.TestReference::OBJECT_LOIC_1_AVAILABILITY_1.'/supprimer';

    private const FLASH_SUCCESS = 'app.controller.user.product.delete_product_unavailability_action.flash.success';

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

    public function testDeleteSuccess(): void
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
