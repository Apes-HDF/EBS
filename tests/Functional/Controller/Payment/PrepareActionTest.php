<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Payment;

use App\Controller\Payment\Group\PrepareAction;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see PrepareAction
 */
final class PrepareActionTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const ROUTE_LIST = '/fr/groupes';

    private const ROUTE_SHOW_CHARGED = self::ROUTE_LIST.'/group-1/'.TestReference::GROUP_1;

    /**
     * Form is posted without CSRF token.
     */
    public function testPrepareActionCsrfException(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('POST', '/en/my-account/payment/'.TestReference::GROUP_OFFER_GROUP_1_1.'/prepare');
        self::assertResponseIsUnprocessable();
    }

    /**
     * Group offer ot found.
     */
    public function testAcceptInvitationNotFoundException(): void
    {
        $client = self::createClient();
        $this->loginAsUser11($client);
        $client->request('POST', '/en/my-account/payment/'.TestReference::UUID_404.'/prepare');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Nominal case: click an offer to initialize the payment process. In the test
     * environment we use the "offline" gateway so the payment is always successful.
     */
    public function testPrepareSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::ROUTE_SHOW_CHARGED);
        $form = $crawler->selectButton('templates.pages.group.show.payment_prepare.form.submit')->form();
        $client->followRedirects();
        $client->submit($form);
        self::assertResponseIsSuccessful();
        self::assertRouteSame('app_group_show');
        self::assertSelectorTextContains('body', 'flash.success');
    }
}
