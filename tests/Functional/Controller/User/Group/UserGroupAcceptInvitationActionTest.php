<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Group;

use App\Entity\Group;
use App\Entity\User;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see UserGroupController::acceptInvitation()
 */
final class UserGroupAcceptInvitationActionTest extends WebTestCase
{
    use KernelTrait;
    use ContainerRepositoryTrait;
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

    /**
     * The accept route must not let an invited user skip the payment of a charged
     * group: the CSRF token of a free group page is valid for any group.
     */
    public function testAcceptInvitationOfChargedGroupIsRefused(): void
    {
        $client = self::createClient();
        // user 11 is also invited to group 1, which has active paying offers
        // (reload the entities: the fixtures leave the inverse sides empty)
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        /** @var User $user */
        $user = $this->getUserRepository()->find(TestReference::USER_11);
        /** @var Group $charged */
        $charged = $this->getGroupRepository()->find(TestReference::GROUP_1);
        self::assertTrue($charged->hasActiveOffers());
        $this->getUserManager()->addInvitation($user, $charged);
        $this->getUserManager()->save($user, true);

        $this->loginAsUser11($client);
        $crawler = $client->request('GET', self::ROUTE_SHOW);
        $token = (string) $crawler->filter('form[action$="/acceptInvitation"] input[name="token"]')->attr('value');
        $client->request('POST', '/en/my-account/groups/'.TestReference::GROUP_1.'/acceptInvitation', ['token' => $token]);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $membership = $this->getUserGroupRepository()->findOneBy(['user' => TestReference::USER_11, 'group' => TestReference::GROUP_1]);
        self::assertNotNull($membership);
        self::assertTrue($membership->getMembership()->isInvited());
    }

    /**
     * Only a pending invitation can be accepted: a member or an admin of the group
     * keeps his status.
     */
    public function testAcceptWithoutInvitationKeepsTheMembership(): void
    {
        $client = self::createClient();
        // sarah has a pending invitation to the free group 2: its page gives a valid token
        /** @var Group $group2 */
        $group2 = $this->getGroupRepository()->findOneBy(['name' => 'Groupe 2 - adhésion gratuite']);
        $this->loginAsSarah($client);
        $crawler = $client->request('GET', self::ROUTE_LIST.'/'.$group2->getSlug().'/'.$group2->getId());
        $token = (string) $crawler->filter('form[action$="/acceptInvitation"] input[name="token"]')->attr('value');
        self::assertNotSame('', $token);

        // sarah is already a member of the private group
        $client->request('POST', '/en/my-account/groups/'.TestReference::GROUP_PRIVATE.'/acceptInvitation', ['token' => $token]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $membership = $this->getUserGroupRepository()->findOneBy(['user' => TestReference::ADMIN_SARAH, 'group' => TestReference::GROUP_PRIVATE]);
        self::assertNotNull($membership);
        self::assertTrue($membership->getMembership()->isMember());
    }
}
