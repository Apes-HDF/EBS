<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Product;

use App\Controller\User\Product\ObjectController;
use App\Entity\Product;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see ObjectController
 */
final class ObjectControllerTest extends WebTestCase
{
    use ReloadDatabaseTrait;
    use KernelTrait;
    use ContainerRepositoryTrait;

    private const NEW_OBJECT_ROUTE = '/fr/mon-compte/nouvel-objet';

    private const EDIT_USER_8_OBJECT_1_ROUTE = '/fr/mon-compte/objets/'.TestReference::SERVICE_USER_16_1.'/editer';

    /**
     * Test required fields.
     */
    public function testCreateObjectValidationError(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        $crawler = $client->request('GET', self::EDIT_USER_8_OBJECT_1_ROUTE);
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('object_form_submit')->form();

        /** @var ChoiceFormField $categoryField */
        $categoryField = $form->get($form->getName().'[category]');
        $categoryField->disableValidation();

        $crawler = $client->submit($form, [
            $form->getName().'[category]' => '',
            $form->getName().'[name]' => '',
            $form->getName().'[description]' => '',
        ]);

        self::assertResponseIsUnprocessable();
        // 4 because there is the upload feedback div already in the response
        self::assertSame(4, $crawler->filter('.invalid-feedback')->count());
    }

    public function testEditObjectForm(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        $crawler = $client->request('GET', self::EDIT_USER_8_OBJECT_1_ROUTE);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('object_form_submit')->form();

        $client->submit($form, [
            $form->getName().'[category]' => TestReference::CATEGORY_OBJECT_1,
            $form->getName().'[name]' => 'outils de poterie',
            $form->getName().'[description]' => 'description test',
            $form->getName().'[visibility]' => 'public',
            $form->getName().'[age]' => '2012',
            $form->getName().'[deposit]' => 5,
            $form->getName().'[preferredLoanDuration]' => '1 journée',
        ]);

        $repo = $this->getProductRepository();
        $objects = $repo->findBy(['owner' => TestReference::USER_16, 'type' => 'object']);
        $editedObject = $repo->get(TestReference::SERVICE_USER_16_1);

        self::assertCount(TestReference::USER_8_OBJECTS_COUNT, $objects);
        self::assertSame('outils de poterie', $editedObject->getName());
        self::assertSame('description test', $editedObject->getDescription());
        self::assertSame('2012', $editedObject->getAge());
        self::assertSame(500, $editedObject->getDeposit());
        self::assertTrue($editedObject->getVisibility()->isPublic());

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testEditObjectFormWithTooLongName(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        $crawler = $client->request('GET', self::EDIT_USER_8_OBJECT_1_ROUTE);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('object_form_submit')->form();

        $client->submit($form, [
            $form->getName().'[category]' => TestReference::CATEGORY_OBJECT_1,
            $form->getName().'[name]' => str_repeat('Lorem ipsum', 100),
            $form->getName().'[description]' => 'description test',
            $form->getName().'[visibility]' => 'public',
            $form->getName().'[age]' => '2012',
            $form->getName().'[deposit]' => 5,
            $form->getName().'[preferredLoanDuration]' => '1 journée',
        ]);

        $repo = $this->getProductRepository();
        $objects = $repo->findBy(['owner' => TestReference::USER_16, 'type' => 'object']);

        self::assertCount(TestReference::USER_8_OBJECTS_COUNT, $objects);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'This value is too long.');
    }

    public function testNewObjectFormWithTooLongName(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::NEW_OBJECT_ROUTE);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('object_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[category]' => TestReference::CATEGORY_OBJECT_1,
            $form->getName().'[name]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam non odio libero. Nulla id fermentum augue, nec tempor mauris. In maximus magna malesuada velit molestie, et ultrices nulla lacinia. Vivamus mauris odio, commodo vel sapien vel, convallis ac..',
            $form->getName().'[description]' => 'test description',
            // $form->getName().'[visibility]' => 'public',
            $form->getName().'[age]' => '2 ans',
            $form->getName().'[deposit]' => 4,
        ]);

        $repo = $this->getProductRepository();
        $objects = $repo->findBy(['owner' => TestReference::USER_16, 'type' => 'object']);

        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'This value is too long.');
        self::assertCount(TestReference::USER_8_OBJECTS_COUNT, $objects);
    }

    public function testNewObjectFormWithStringDepositValue(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::NEW_OBJECT_ROUTE);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('object_form_submit')->form();

        $client->submit($form, [
            $form->getName().'[category]' => TestReference::CATEGORY_OBJECT_1,
            $form->getName().'[name]' => 'Diable',
            $form->getName().'[description]' => 'test description',
            $form->getName().'[age]' => '2 ans',
            $form->getName().'[deposit]' => 'deux',
        ]);

        $repo = $this->getProductRepository();
        $objects = $repo->findBy(['owner' => TestReference::USER_16, 'type' => 'object']);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'Please enter a valid money amount.');
        self::assertCount(TestReference::USER_8_OBJECTS_COUNT, $objects);
    }

    public function testNewObjectFormSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::NEW_OBJECT_ROUTE);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('object_form_submit')->form();

        $client->submit($form, [
            $form->getName().'[category]' => TestReference::CATEGORY_OBJECT_1,
            $form->getName().'[name]' => 'Diable',
            $form->getName().'[description]' => 'test description',
            // $form->getName().'[visibility]' => 'public',
            $form->getName().'[age]' => '2 ans',
        ]);

        $repo = $this->getProductRepository();
        $objects = $repo->findBy(['owner' => TestReference::USER_17, 'type' => 'object']);

        /** @var Product $newObject */
        $newObject = $repo->findOneBy(['name' => 'Diable']);
        self::assertCount(TestReference::USER_17_SERVICES_COUNT + 1, $objects);
        self::assertNull($newObject->getDepositReal());
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testEditObjectFormForbidden(): void
    {
        $client = self::createClient();
        $this->loginAsUser11($client);
        $client->request('GET', self::EDIT_USER_8_OBJECT_1_ROUTE);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
