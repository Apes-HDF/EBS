<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\MenuCrudController;
use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MenuCrudControllerTest extends WebTestCase
{
    use KernelTrait;

    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        $menuRepo = $client->getContainer()->get(MenuRepository::class);
        /** @var Menu $menu */
        $menu = $menuRepo->getByCode('menu');

        // list
        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', MenuCrudController::class));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', MenuCrudController::class, $menu->getId()));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', MenuCrudController::class, $menu->getId()));
        self::assertResponseIsSuccessful();
    }
}
