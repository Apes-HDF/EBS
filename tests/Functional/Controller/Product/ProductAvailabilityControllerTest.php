<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Product;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see ProductAvailabilityControllerTest
 */
final class ProductAvailabilityControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use KernelTrait;

    private const ROUTE = '/fr/mon-compte/mes-produits/'.TestReference::OBJECT_LOIC_1.'/disponibilites';

    private const ROUTE_404 = '/fr/mon-compte/mes-produits/'.TestReference::UUID_404.'disponibilites';

    private const FORM_ID = 'create_product_availability';

    private const FLASH_SUCCESS = 'app.controller.user.product.product_availability_controller.success';

    /**
     * Product not found.
     */
    public function testNotFoundException(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', self::ROUTE_404);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testFormSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::ROUTE);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::FORM_ID.'_submit')->form();
        $date = new \DateTimeImmutable('+ 1 day');
        $client->submit($form, [
            $form->getName().'[startAt]' => $date->format('Y-m-d'),
            $form->getName().'[endAt]' => $date->modify('+1 day')->format('Y-m-d'),
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS);
    }
}
