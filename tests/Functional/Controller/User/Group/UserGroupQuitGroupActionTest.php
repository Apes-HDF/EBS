<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Group;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see UserGroupController::quitGroup()
 */
final class UserGroupQuitGroupActionTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const ROUTE_LIST = '/fr/groupes';

    private const ROUTE_SHOW = self::ROUTE_LIST.'/groupe-7/'.TestReference::GROUP_7;
    private const ROUTE_SHOW_GROUP_1 = self::ROUTE_LIST.'/groupe-1/'.TestReference::GROUP_1;

    /**
     * Nominal case: quit a group I am member of.
     */
    public function testQuiGroupSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', self::ROUTE_SHOW);
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('templates.pages.group.show.form.quit_group.submit')->form();
        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'flash.success');
        self::assertRouteSame('app_group_show');
    }

    /**
     * Quit a group where I have associated products.
     */
    public function testQuitGroupWithProductsSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsPlaceApes($client);

        $crawler = $client->request('GET', self::ROUTE_SHOW_GROUP_1);
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('templates.pages.group.show.form.quit_group.submit')->form();
        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'flash.success');
        self::assertRouteSame('app_group_show');
    }
}
