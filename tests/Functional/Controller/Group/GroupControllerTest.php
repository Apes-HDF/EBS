<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Group;

use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see GroupController
 */
final class GroupControllerTest extends WebTestCase
{
    use ReloadDatabaseTrait;
    use KernelTrait;
    use ContainerRepositoryTrait;

    private const ROUTE_LIST = '/fr/groupes';

    public function testListSuccess(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_LIST);
        self::assertResponseIsSuccessful();
    }

    public function testNotFoundError(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_LIST.'/foobar/'.TestReference::UUID_404);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testShowSuccess(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_LIST.'/group-1/'.TestReference::GROUP_1);
        self::assertResponseIsSuccessful();
    }

    public function testShowLoggedSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser11($client);
        $client->request('GET', self::ROUTE_LIST.'/group-1/'.TestReference::GROUP_1.'/invitation');
        self::assertResponseIsSuccessful();
    }

    public function testSearchGroup(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_LIST);
        self::assertSame(9, $client->getCrawler()->filter('.group-test')->count());

        $form = $client->getCrawler()->selectButton('group_select_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[q]' => 'Groupe 2',
        ]);

        self::assertSame(1, $client->getCrawler()->filter('.group-test')->count());
    }

    public function testMemberList(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_LIST.'/group-1/'.TestReference::GROUP_1.'/membres');
        self::assertResponseIsSuccessful();
    }

    public function testSearchMember(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', self::ROUTE_LIST.'/group-1/'.TestReference::GROUP_1.'/membres');
        self::assertSame(TestReference::GROUP_1_MEMBER_COUNT, $crawler->filter('.group-test')->count());

        $form = $client->getCrawler()->selectButton('group_select_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[q]' => 'sarah',
        ]);

        self::assertSame(1, $client->getCrawler()->filter('.group-test')->count());
    }

    public function testDisplayAdminButton(): void
    {
        $client = self::createClient();

        $this->loginAsUser16($client);
        $client->request('GET', self::ROUTE_LIST.'/group-1/'.TestReference::GROUP_1.'/membres');
        self::assertSame(1, $client->getCrawler()->filter('.admin-button-test')->count());

        $this->loginAsSarah($client);
        $client->request('GET', self::ROUTE_LIST.'/group-1/'.TestReference::GROUP_1.'/membres');
        self::assertSame(0, $client->getCrawler()->filter('.admin-button-test')->count());
    }
}
