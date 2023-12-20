<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Test\KernelTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * When logged as a group admin.
 */
final class DashboardControllerAsGroupAdminTest extends WebTestCase
{
    use KernelTrait;

    /**
     * @see DashboardController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', '/admin');
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
