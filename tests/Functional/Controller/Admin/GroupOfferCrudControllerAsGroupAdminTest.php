<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\GroupOfferCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Specific tests when logged as a group administartor.
 */
final class GroupOfferCrudControllerAsGroupAdminTest extends WebTestCase
{
    use KernelTrait;

    /**
     * @see GroupOfferCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        // list+custom filters
        $filters = '&filters[group]='.TestReference::GROUP_1;
        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', GroupOfferCrudController::class.'&'.$filters));
        self::assertResponseIsSuccessful();

        // new (groups are restricted)
        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'new', GroupOfferCrudController::class));
        self::assertResponseIsSuccessful();
    }
}
