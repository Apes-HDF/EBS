<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\PaymentCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PaymentCrudControllerTest extends WebTestCase
{
    use KernelTrait;

    /**
     * @see PaymentCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list + filters
        $filters = '&filters[user]='.TestReference::USER_16;
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', PaymentCrudController::class).'&'.$filters);
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', PaymentCrudController::class, TestReference::PAYMENT_USER_16_1));
        self::assertResponseIsSuccessful();
    }
}
