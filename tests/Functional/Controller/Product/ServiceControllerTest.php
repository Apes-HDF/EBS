<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Product;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ServiceControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use KernelTrait;

    private const EDIT_USER_8_SERVICE_1_ROUTE = '/fr/mon-compte/services/'.TestReference::OBJECT_USER_16_1.'/editer';
    private const NEW_SERVICE_ROUTE = '/fr/mon-compte/nouveau-service';

    public function testEditServiceForm(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        $crawler = $client->request('GET', self::EDIT_USER_8_SERVICE_1_ROUTE);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('service_form_submit')->form();

        $client->submit($form, [
            $form->getName().'[category]' => TestReference::CATEGORY_SERVICE_1,
            $form->getName().'[name]' => 'jardinage',
            $form->getName().'[description]' => 'description',
            $form->getName().'[duration]' => '1 jour',
            $form->getName().'[visibility]' => 'restricted',
            $form->getName().'[groups]' => [TestReference::GROUP_1],
        ]);

        $container = $client->getContainer();
        $repo = $container->get(ProductRepository::class);
        $services = $repo->findBy(['owner' => TestReference::USER_16, 'type' => 'service']);
        /** @var Product $editedService */
        $editedService = $repo->find('1edae186-1b1e-6da8-8b71-e114a7d26c2e');

        self::assertCount(TestReference::USER_8_SERVICES_COUNT, $services);
        self::assertSame('jardinage', $editedService->getName());
        self::assertSame('description', $editedService->getDescription());
        self::assertSame('1 jour', $editedService->getDuration());
        self::assertSame('restricted', $editedService->getVisibility()->value);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testEditServiceFormWithTooLongName(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $crawler = $client->request('GET', self::EDIT_USER_8_SERVICE_1_ROUTE);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('service_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[category]' => TestReference::CATEGORY_SERVICE_1,
            $form->getName().'[name]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam non odio libero. Nulla id fermentum augue, nec tempor mauris. In maximus magna malesuada velit molestie, et ultrices nulla lacinia. Vivamus mauris odio, commodo vel sapien vel, convallis ac..',
            $form->getName().'[description]' => 'description',
            $form->getName().'[duration]' => '1 jour',
        ]);

        $container = $client->getContainer();
        $repo = $container->get(ProductRepository::class);
        $services = $repo->findBy(['owner' => TestReference::USER_16, 'type' => 'service']);

        self::assertCount(TestReference::USER_8_SERVICES_COUNT, $services);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'This value is too long.');
    }

    public function testNewServiceFormWithTooLongName(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $crawler = $client->request('GET', self::NEW_SERVICE_ROUTE);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('service_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[category]' => TestReference::CATEGORY_SERVICE_1,
            $form->getName().'[name]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam non odio libero. Nulla id fermentum augue, nec tempor mauris. In maximus magna malesuada velit molestie, et ultrices nulla lacinia. Vivamus mauris odio, commodo vel sapien vel, convallis ac..',
            $form->getName().'[description]' => 'test description',
            $form->getName().'[duration]' => '2 jours',
        ]);

        $container = $client->getContainer();
        $repo = $container->get(ProductRepository::class);
        $services = $repo->findBy(['owner' => TestReference::USER_16, 'type' => 'service']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'This value is too long.');
        self::assertCount(TestReference::USER_8_SERVICES_COUNT, $services);
    }

    public function testNewServiceForm(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $crawler = $client->request('GET', self::NEW_SERVICE_ROUTE);
        self::assertResponseIsSuccessful();

        $imageName = 'apes.png';
        $image = realpath(__DIR__.'/../../../Fixtures/images/'.$imageName);
        $uploadedFile = new UploadedFile((string) $image, $imageName);
        $form = $crawler->selectButton('service_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[category]' => TestReference::CATEGORY_SERVICE_1,
            $form->getName().'[name]' => 'Aide bricolage',
            $form->getName().'[description]' => 'test description',
            $form->getName().'[duration]' => '2 jours',
            $form->getName().'[images]' => [$uploadedFile],
        ]);

        $container = $client->getContainer();
        $repo = $container->get(ProductRepository::class);
        $services = $repo->findBy(['owner' => TestReference::USER_17, 'type' => 'service']);
        self::assertCount(TestReference::USER_17_SERVICES_COUNT + 1, $services);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
