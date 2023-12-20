<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

class UserProductsControllerTest extends WebTestCase
{
    use ContainerRepositoryTrait;
    use ReloadDatabaseTrait;
    use KernelTrait;

    private const ROUTE_LIST_SERVICES = '/fr/mon-compte/mes-services';
    private const ROUTE_LIST_OBJECTS = '/fr/mon-compte/mes-objets';

    public function testUserServices(): void
    {
        $client = self::createClient();
        $this->loginAsSarah($client);
        $crawler = $client->request('GET', self::ROUTE_LIST_SERVICES);
        self::assertResponseIsSuccessful();
        self::assertSame(TestReference::SARAH_SERVICES_COUNT, $crawler->filter('[data-test-product]')->count());

        $form = $crawler->selectButton('service_category_select_form_submit')->form();
        /** @var ChoiceFormField $field */
        $field = $form[$form->getName().'[category]'];
        $field->select(TestReference::SUB_CATEGORY_SERVICE_1);
        $crawler = $client->submit($form);
        self::assertResponseIsSuccessful();
        self::assertSame(TestReference::SARAH_SERVICES_COUNT - 1, $crawler->filter('[data-test-product]')->count());
    }

    public function testUserObjects(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $crawler = $client->request('GET', self::ROUTE_LIST_OBJECTS);
        self::assertResponseIsSuccessful();
        self::assertSame(TestReference::USER_8_OBJECTS_COUNT, $crawler->filter('[data-test-product]')->count());
    }

    public function testUserObjectsFilterByCategorySuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::ROUTE_LIST_OBJECTS);
        self::assertResponseIsSuccessful();
        self::assertSame(TestReference::ADMIN_LOIC_OBJECTS_COUNT, $crawler->filter('[data-test-product]')->count());

        $form = $crawler->selectButton('object_category_select_form_submit')->form();
        /** @var ChoiceFormField $field */
        $field = $form[$form->getName().'[category]'];
        $field->select(TestReference::CATEGORY_OBJECT_2);
        $crawler = $client->submit($form);
        self::assertResponseIsSuccessful();
        self::assertSame(1, $crawler->filter('[data-test-product]')->count());
    }
}
