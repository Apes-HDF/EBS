<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\GroupCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Specific case when logger a a group admin (ROLE_GROUP_ADMIN).
 */
final class GroupCrudControllerAsGroupAdminTest extends WebTestCase
{
    use KernelTrait;

    /**
     * A group admin can access the admin interface and see the group he handles.
     *
     * @see GroupCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        // list
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', GroupCrudController::class));
        self::assertResponseIsSuccessful();
    }
}
