<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\UserCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserCrudControllerTest extends WebTestCase
{
    use KernelTrait;

    /**
     * @see UserCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', UserCrudController::class));

        // list + filters
        $filters = '&filters[group]='.TestReference::GROUP_1;
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', UserCrudController::class).'&'.$filters);
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', UserCrudController::class, TestReference::USER_17));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', UserCrudController::class, TestReference::USER_16));
        self::assertResponseIsSuccessful();
    }

    /**
     * Test that the validation is applied for the phone field.
     */
    public function testValidation(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', UserCrudController::class, TestReference::USER_17));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE)->form();
        $client->submit($form, [
            $form->getName().'[phone]' => 'foobar',
        ]);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('div', 'app.controller.admin.abstract_user_crud_controller.field.phone.help');
    }

    /**
     * Nominal case for the edit action.
     */
    public function testEditAction(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', UserCrudController::class, TestReference::USER_17));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE)->form();
        $client->submit($form, [
            $form->getName().'[phone]' => '+33610010203',
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('div', 'app.controller.admin.abstract_user_crud_controller.field.phone.help');
    }

    public function testPromoteToAdmin(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', UserCrudController::class));

        $dataId = \sprintf("[data-id='%s']", TestReference::USER_17);
        self::assertSelectorTextContains($dataId, 'action.promoteToAdmin');

        $link = $crawler->filter('.action-promoteToAdmin')->link();
        $client->click($link);

        self::assertResponseRedirects();
    }
}
