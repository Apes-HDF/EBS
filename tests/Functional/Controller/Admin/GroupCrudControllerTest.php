<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\GroupCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GroupCrudControllerTest extends WebTestCase
{
    use KernelTrait;

    /**
     * @see GroupCrudController
     */
    public function testGroupCrudController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list
        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', GroupCrudController::class));
        self::assertResponseIsSuccessful();

        // list + filter
        $filters = 'filters[type]=private&filter[membership]=free';
        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', GroupCrudController::class.'&'.$filters));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', GroupCrudController::class, TestReference::GROUP_1));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', GroupCrudController::class, TestReference::GROUP_1));
        self::assertResponseIsSuccessful();

        // new
        $crawler = $client->request('GET', sprintf(TestReference::ADMIN_URL, 'new', GroupCrudController::class));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE_AND_RETURN)->form();
        $client->submit($form, [
            $form->getName().'[name]' => 'Groupe public',
            $form->getName().'[type]' => 'public',
            $form->getName().'[description]' => 'very nice group',
            $form->getName().'[url]' => 'https://www.example.com',
            $form->getName().'[membership]' => 'free',
            $form->getName().'[invitationByAdmin]' => true,
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideTestInviateActionSuccess(): iterable
    {
        yield [TestReference::GROUP_1, 'userinvited@example.com', false]; // Invite a user that isn't already in the database.
        yield [TestReference::GROUP_1, TestReference::USER_17_EMAIL, true]; // Invite a user that is already in the database
    }

    /**
     * Invite a user that isn't already in the database.
     *
     * @dataProvider provideTestInviateActionSuccess
     *
     * @see GroupCrudController::invite()
     */
    public function testInviteActionSuccess(string $groupId, string $email, bool $hasNotification): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // custom invite
        $crawler = $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'invite', GroupCrudController::class, $groupId));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('group_invitation_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[email]' => $email,
        ]);
        self::assertEmailCount(1);
        // only for existing users
        if ($hasNotification) {
            self::assertNotificationCount(1);
        }

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'app.controller.admin.group_crud_controller.invite.flash.success');
    }

    public function testOffersListButtonOnIndex(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', GroupCrudController::class));
        $dataId = sprintf("[data-id='%s']", TestReference::GROUP_1);
        $offersListlink = $client->getCrawler()->filter($dataId.' .action-offersList')->link();
        $client->click($offersListlink);
        self::assertResponseRedirects();
        $client->followRedirect();
    }

    public function testExport(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'export', GroupCrudController::class));
        self::assertResponseIsSuccessful();
    }
}
