<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\ServiceRequest;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see MyLendingsAction
 */
final class MyLendingsActionTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/mes-prets';

    public function testListSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', self::ROUTE);
        self::assertResponseIsSuccessful();
        self::assertSame(3, $client->getCrawler()->filter('.conversation-test')->count());
    }

    public function testListByProduct(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', self::ROUTE.'?user_lending_product_select_form[product][]='.TestReference::OBJECT_LOIC_1);
        self::assertResponseIsSuccessful();

        self::assertSame(2, $client->getCrawler()->filter('.conversation-test')->count());
    }
}
