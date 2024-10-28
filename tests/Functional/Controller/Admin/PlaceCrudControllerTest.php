<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\PlaceCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PlaceCrudControllerTest extends WebTestCase
{
    use KernelTrait;

    /**
     * @see PlaceCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', PlaceCrudController::class));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', PlaceCrudController::class, TestReference::PLACE_7));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', PlaceCrudController::class, TestReference::PLACE_7));
        self::assertResponseIsSuccessful();
    }
}
