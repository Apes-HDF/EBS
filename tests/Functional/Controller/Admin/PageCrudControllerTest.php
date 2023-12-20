<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\PageCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PageCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list
        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', PageCrudController::class));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', PageCrudController::class, TestReference::PAGE_1));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', PageCrudController::class, TestReference::PAGE_1));
        self::assertResponseIsSuccessful();

        $client->clickLink('page.action.link');
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        // new
        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'new', PageCrudController::class));
        self::assertResponseIsSuccessful();
    }
}
