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

    /**
     * The members of a private group are only listed to its confirmed members.
     */
    public function testPrivateGroupMemberListAccess(): void
    {
        $client = self::createClient();
        $url = self::ROUTE_LIST.'/private/'.TestReference::GROUP_PRIVATE.'/membres';

        $client->request('GET', $url);
        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));

        // not a member
        $this->loginAsUser($client);
        $client->request('GET', $url);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // invitation not accepted yet
        $this->loginAsUser11($client);
        $client->request('GET', $url);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // once the invitation is accepted, user 11 is a confirmed member
        $membership = $this->getUserGroupRepository()->findOneBy(['user' => TestReference::USER_11, 'group' => TestReference::GROUP_PRIVATE]);
        self::assertNotNull($membership);
        $membership->setMember();
        $this->getUserGroupRepository()->save($membership, true);
        $client->request('GET', $url);
        self::assertResponseIsSuccessful();

        // platform admin, not a member of the group
        $this->loginAsKevin($client);
        $client->request('GET', $url);
        self::assertResponseIsSuccessful();
    }

    /**
     * The public member search must not be usable to test email addresses.
     */
    public function testSearchMemberDoesNotMatchEmail(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE_LIST.'/group-1/'.TestReference::GROUP_1.'/membres');
        $form = $client->getCrawler()->selectButton('group_select_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[q]' => 'example.com',
        ]);

        self::assertSame(0, $client->getCrawler()->filter('.group-test')->count());
    }
}
