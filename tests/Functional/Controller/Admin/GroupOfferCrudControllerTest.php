<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\GroupOfferCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GroupOfferCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    /**
     * @see GroupOfferCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list+custom filters
        $filters = '&filters[group]='.TestReference::GROUP_1;
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', GroupOfferCrudController::class.'&'.$filters));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', GroupOfferCrudController::class, TestReference::GROUP_OFFER_GROUP_1_1));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', GroupOfferCrudController::class, TestReference::GROUP_OFFER_GROUP_1_1));
        self::assertResponseIsSuccessful();

        // new
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'new', GroupOfferCrudController::class));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE_AND_RETURN)->form();
        $client->submit($form, [
            $form->getName().'[group]' => TestReference::GROUP_1,
            $form->getName().'[name]' => 'New special offer',
            $form->getName().'[type]' => 'monthly',
            $form->getName().'[price]' => 66,
            $form->getName().'[currency]' => 'EUR',
            $form->getName().'[active]' => false,
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
