<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Group;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see UserGroupController::acceptInvitation()
 */
final class UserGroupAcceptInvitationActionTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE_LIST = '/fr/groupes';

    private const ROUTE_SHOW = self::ROUTE_LIST.'/nice-private-group/'.TestReference::GROUP_PRIVATE;

    /**
     * Form is posted without CSRF token.
     */
    public function testAcceptInvitationCsrfException(): void
    {
        $client = self::createClient();
        $this->loginAsUser11($client);
        $client->request('POST', '/en/my-account/groups/'.TestReference::GROUP_PRIVATE.'/acceptInvitation');
        self::assertResponseIsUnprocessable();
    }

    /**
     * Group not found.
     */
    public function testAcceptInvitationNotFoundException(): void
    {
        $client = self::createClient();
        $this->loginAsUser11($client);
        $client->request('POST', '/en/my-account/groups/'.TestReference::UUID_404.'/acceptInvitation');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Nominal case: accept the invitation by clicking on the link.
     */
    public function testAcceptInvitationSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser11($client);
        $crawler = $client->request('GET', self::ROUTE_SHOW);
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('templates.pages.group.show.form.accept_invitation.submit')->form();
        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertRouteSame('app_group_show');
    }
}
