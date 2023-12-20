<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Group;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see UserGroupController
 */
final class UserGroupControllerTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE_LIST = '/fr/groupes';

    private const ROUTE_SHOW_FREE = self::ROUTE_LIST.'/lecourtcircuit-fr/'.TestReference::GROUP_5;

    private const ROUTE_USER_LIST = '/fr/mon-compte/mes-groupes';

    /**
     * Form is posted without CSRF token.
     */
    public function testJoinCsrfException(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('POST', '/en/my-account/groups/'.TestReference::GROUP_5.'/join');
        self::assertResponseIsUnprocessable();
    }

    /**
     * Group not found.
     */
    public function testJoinNotFoundException(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('POST', '/en/my-account/groups/'.TestReference::UUID_404.'/join');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Nominal case: join a public group with free access.
     */
    public function testJoinSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::ROUTE_SHOW_FREE);
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('templates.pages.group.show.group_join.form.submit')->form();
        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertRouteSame('app_group_show');
    }

    /**
     * Test list of user groups.
     */
    public function testList(): void
    {
        $client = self::createClient();
        $this->loginAsSarah($client);
        $client->request('GET', self::ROUTE_USER_LIST);
        self::assertResponseIsSuccessful();
        self::assertSame(1, $client->getCrawler()->filter('.invitation-test')->count());
        self::assertSame(3, $client->getCrawler()->filter('.group-test')->count());
    }
}
