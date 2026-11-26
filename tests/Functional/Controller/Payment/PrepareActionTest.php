<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Payment;

use App\Controller\Payment\Group\PrepareAction;
use App\Enum\Group\GroupType;
use App\Test\ContainerRepositoryTrait;
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
    use ContainerRepositoryTrait;

    private const ROUTE_LIST = '/fr/groupes';

    private const ROUTE_SHOW_CHARGED = self::ROUTE_LIST.'/group-1/'.TestReference::GROUP_1;

    private const ROUTE_SHOW_CHARGED_7 = self::ROUTE_LIST.'/group-7/'.TestReference::GROUP_7;

    private const SUBMIT_BUTTON = 'templates.pages.group.show.payment_prepare.form.submit';

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
        $client->disableReboot(); // keep the database transaction until the end of the payment
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::ROUTE_SHOW_CHARGED);
        $form = $crawler->selectButton('templates.pages.group.show.payment_prepare.form.submit')->form();
        $client->followRedirects();
        $client->submit($form);
        self::assertResponseIsSuccessful();
        self::assertRouteSame('app_group_show');
        self::assertSelectorTextContains('body', 'flash.success');
    }

    /**
     * A private group can only be joined with an invitation, even when it is charged:
     * a user without invitation can't pay one of its offers (the form was retrieved
     * while the group was still public, or forged with the offer id).
     */
    public function testPreparePrivateGroupWithoutInvitationIsForbidden(): void
    {
        $client = self::createClient();
        $client->disableReboot(); // keep the same database transaction between the two requests
        $this->loginAsUser($client);
        $crawler = $client->request('GET', self::ROUTE_SHOW_CHARGED);
        $form = $crawler->selectButton(self::SUBMIT_BUTTON)->form();
        $paymentCount = $this->getPaymentRepository()->count([]);

        $this->setGroupType(TestReference::GROUP_1, GroupType::PRIVATE);
        $client->submit($form);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertSame($paymentCount, $this->getPaymentRepository()->count([]));
    }

    /**
     * The offers of a private group are not displayed to a user without invitation.
     */
    public function testPrivateGroupOffersHiddenWithoutInvitation(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $this->setGroupType(TestReference::GROUP_1, GroupType::PRIVATE);
        $crawler = $client->request('GET', self::ROUTE_SHOW_CHARGED);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'templates.pages.group.show.private_invitation_only');
        self::assertCount(0, $crawler->selectButton(self::SUBMIT_BUTTON));
    }

    /**
     * A user invited to a charged private group pays an offer and becomes a member.
     */
    public function testPreparePrivateGroupWithInvitationSuccess(): void
    {
        $client = self::createClient();
        $client->disableReboot(); // keep the database transaction until the end of the payment
        $this->loginAsUser11($client);
        $this->setGroupType(TestReference::GROUP_7, GroupType::PRIVATE);
        $crawler = $client->request('GET', self::ROUTE_SHOW_CHARGED_7);
        $form = $crawler->selectButton(self::SUBMIT_BUTTON)->form();
        $client->followRedirects();
        $client->submit($form);
        self::assertResponseIsSuccessful();
        self::assertRouteSame('app_group_show');
        self::assertSelectorTextContains('body', 'flash.success');

        $user = $this->getUserRepository()->get(TestReference::USER_11);
        $group = $this->getGroupRepository()->get(TestReference::GROUP_7);
        self::assertFalse($group->getType()->isPublic());
        self::assertTrue($user->getGroupMembership($group)?->getMembership()->isMember());
    }

    /**
     * The invitation is removed while the user is paying: the payment is captured
     * but the user doesn't join the private group and gets a warning (no error 500).
     */
    public function testDonePrivateGroupInvitationRemovedDuringPayment(): void
    {
        $client = self::createClient();
        $client->disableReboot(); // keep the database transaction until the end of the payment
        $this->loginAsUser11($client);
        $this->setGroupType(TestReference::GROUP_7, GroupType::PRIVATE);
        $crawler = $client->request('GET', self::ROUTE_SHOW_CHARGED_7);
        $form = $crawler->selectButton(self::SUBMIT_BUTTON)->form();
        $client->submit($form); // prepare
        self::assertResponseRedirects();
        $client->followRedirect(); // capture (offline gateway)
        self::assertResponseRedirects();

        $user = $this->getUserRepository()->get(TestReference::USER_11);
        $group = $this->getGroupRepository()->get(TestReference::GROUP_7);
        $invitation = $user->getGroupMembership($group);
        self::assertNotNull($invitation);
        $this->getUserGroupRepository()->remove($invitation, true);
        $this->fixDoctrine();

        $client->followRedirect(); // done
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertRouteSame('app_group_show');
        self::assertSelectorTextContains('body', 'app.controller.payment.group.done_action.flash.membership_refused');
        self::assertNull($this->getUserGroupRepository()->findOneBy(['user' => TestReference::USER_11, 'group' => TestReference::GROUP_7]));
    }

    private function setGroupType(string $groupId, GroupType $type): void
    {
        $groupRepository = $this->getGroupRepository();
        $groupRepository->save($groupRepository->get($groupId)->setType($type), true);
    }
}
