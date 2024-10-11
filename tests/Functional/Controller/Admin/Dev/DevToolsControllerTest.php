<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin\Dev;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DevToolsControllerTest extends WebTestCase
{
    use KernelTrait;

    /**
     * @see DevToolsController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', \sprintf(TestReference::ADMIN_URL_CUSTOM_CONTROLLER, 'admin_dev_tools'));
        self::assertResponseIsSuccessful();
    }
}
