<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\AdministratorCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AdministratorCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    public function testGroupAdminAccessDeniedException(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', AdministratorCrudController::class));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @see AdministratorCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', AdministratorCrudController::class));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', AdministratorCrudController::class, TestReference::ADMIN_CAMILLE));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', AdministratorCrudController::class, TestReference::ADMIN_CAMILLE));
        self::assertResponseIsSuccessful();

        // new
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'new', AdministratorCrudController::class));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE_AND_RETURN)->form();
        $client->submit($form, [
            $form->getName().'[email]' => 'new_admin@example.com',
            $form->getName().'[firstname]' => 'Foo',
            $form->getName().'[lastname]' => 'Bar',
            $form->getName().'[plainPassword][first]' => TestReference::PASSWORD,
            $form->getName().'[plainPassword][second]' => TestReference::PASSWORD,
            $form->getName().'[enabled]' => true,
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    /**
     * Test that the default validation is applied for the email field.
     */
    public function testNewValidation(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'new', AdministratorCrudController::class));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE_AND_RETURN)->form();
        $client->submit($form, [
            $form->getName().'[email]' => 'a@b',
            $form->getName().'[firstname]' => 'Foo',
            $form->getName().'[lastname]' => 'Bar',
            $form->getName().'[plainPassword][first]' => 'a',
            $form->getName().'[plainPassword][second]' => 'a',
            $form->getName().'[enabled]' => true,
        ]);
        self::assertResponseIsSuccessful(); // no 422 with EA
        self::assertSelectorTextContains('div.field-email', 'This value is not a valid email address');
        self::assertSelectorTextContains('div', 'This value is too short'); // EA bug: the div class is not correct
    }

    /**
     * @see AbstractUserCrudController::connectAs()
     */
    public function testConnectAs(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'connectAs', AdministratorCrudController::class, TestReference::ADMIN_CAMILLE));
        self::assertResponseRedirects();
        $client->followRedirects();
        self::assertResponseRedirects();
    }

    /**
     * @see AbstractUserCrudController::export()
     */
    public function testExport(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', AdministratorCrudController::class));
        self::assertResponseIsSuccessful();

        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'export', AdministratorCrudController::class));
        self::assertResponseIsSuccessful();
    }

    public function testDeleteSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'delete', AdministratorCrudController::class, TestReference::PLACE_7));
        self::assertResponseRedirects();
        $client->followRedirects();
        self::assertResponseRedirects();
    }

    /**
     * Can't delete the main admin account.
     *
     * @see AdministratorCrudController::delete()
     */
    public function testDeleteAccessDeniedException(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'delete', AdministratorCrudController::class, TestReference::ADMIN_CAMILLE));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
