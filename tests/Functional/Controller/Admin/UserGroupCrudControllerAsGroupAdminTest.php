<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\UserGroupCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Specific tests when logged as a group administrator.
 */
final class UserGroupCrudControllerAsGroupAdminTest extends WebTestCase
{
    use KernelTrait;

    /**
     * @see UserGroupCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        // list+custom filters
        $filters = '&filters[group]='.TestReference::GROUP_1.'&filters[user]='.TestReference::ADMIN_CAMILLE;
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', UserGroupCrudController::class.'&'.$filters));
        self::assertResponseIsSuccessful();

        // new (groups & users are restricted)
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'new', UserGroupCrudController::class));
        self::assertResponseIsSuccessful();
    }
}
