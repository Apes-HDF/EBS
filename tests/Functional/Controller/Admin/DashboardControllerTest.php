<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Test\KernelTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DashboardControllerTest extends WebTestCase
{
    use KernelTrait;

    /**
     * @see DashboardController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/admin');
        self::assertResponseIsSuccessful();
    }
}
