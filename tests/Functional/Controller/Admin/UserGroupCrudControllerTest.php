<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\UserGroupCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserGroupCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    /**
     * @see UserGroupCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list+custom filters
        $filters = '&filters[group]='.TestReference::GROUP_1;
        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', UserGroupCrudController::class.'&'.$filters));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', UserGroupCrudController::class, TestReference::USER_GROUP_LOIC_GROUP_7));
        self::assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array{0: string, 1: bool}>
     */
    public function provideEditFormSuccess(): iterable
    {
        yield ['admin', false];
        yield ['admin', true];
    }

    /**
     * @dataProvider provideEditFormSuccess
     */
    public function testEditFormSuccess(string $role, bool $mainAdminAccount): void
    {
        $client = self::createClient();
        $this->loginAsSarah($client);
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', UserGroupCrudController::class, TestReference::USER_GROUP_LOIC_GROUP_7));

        $form = $client->getCrawler()->selectButton('ea[newForm][btn]')->form();
        $client->submit($form, [
            $form->getName().'[membership]' => $role,
            $form->getName().'[mainAdminAccount]' => $mainAdminAccount,
        ]);
        self::assertEmailCount(1);
        self::assertNotificationCount(1);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
