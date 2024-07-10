<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\PlatformOfferCrudController;
use App\Enum\OfferType;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PlatformOfferCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    /**
     * @see PlatformOfferCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list+custom filters
        $filters = '&filters[type]='.(OfferType::MONTHLY->isMonthly() ? '1' : '0');

        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', PlatformOfferCrudController::class.'&'.$filters));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', PlatformOfferCrudController::class, TestReference::PLATFORM_OFFER_1));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', PlatformOfferCrudController::class, TestReference::PLATFORM_OFFER_1));
        self::assertResponseIsSuccessful();

        // new
        $crawler = $client->request('GET', sprintf(TestReference::ADMIN_URL, 'new', PlatformOfferCrudController::class));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE_AND_RETURN)->form();
        $client->submit($form, [
            $form->getName().'[name]' => 'New special offer',
            $form->getName().'[type]' => 'yearly',
            $form->getName().'[price]' => 490,
            $form->getName().'[currency]' => 'EUR',
            $form->getName().'[active]' => false,
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
