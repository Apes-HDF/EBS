<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\GroupCrudController;
use App\Controller\Admin\GroupOfferCrudController;
use App\Controller\Admin\UserGroupCrudController;
use App\Entity\GroupOffer;
use App\Entity\UserGroup;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * A group admin can only reach the groups, memberships and offers of the groups
 * he administrates, whatever the action and the entity id given in the URL.
 *
 * @see GroupAdminEntitySubscriber
 */
final class GroupAdminCrossGroupAccessTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use KernelTrait;
    use ContainerRepositoryTrait;

    private static function url(string $action, string $controller, string $entityId): string
    {
        return \sprintf(TestReference::ADMIN_URL, $action, $controller).'&entityId='.$entityId;
    }

    /**
     * @return array<string, array{string, string}>
     */
    private function otherGroupEntities(): array
    {
        /** @var GroupOffer $offer */
        $offer = $this->getGroupOfferRepository()->findOneBy(['group' => TestReference::GROUP_7]);

        return [
            'group' => [GroupCrudController::class, TestReference::GROUP_7],
            'membership' => [UserGroupCrudController::class, TestReference::USER_GROUP_LOIC_GROUP_7],
            'offer' => [GroupOfferCrudController::class, (string) $offer->getId()],
        ];
    }

    public function testOtherGroupEntitiesAreForbidden(): void
    {
        $client = self::createClient();
        // user 16 is admin of group 1 only
        $this->loginAsUser16($client);

        foreach ($this->otherGroupEntities() as $name => [$controller, $id]) {
            foreach (['detail', 'edit'] as $action) {
                $client->request('GET', self::url($action, $controller, $id));
                self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, $name.' '.$action);
            }
            $client->request('POST', self::url('delete', $controller, $id));
            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, $name.' delete');
        }

        foreach (['invite', 'redirectToOffersList'] as $action) {
            $client->request('GET', self::url($action, GroupCrudController::class, TestReference::GROUP_7));
            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, 'group '.$action);
        }
    }

    public function testOwnGroupEntitiesAreAllowed(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        /** @var UserGroup $membership */
        $membership = $this->getUserGroupRepository()->findOneBy(['user' => TestReference::USER_16, 'group' => TestReference::GROUP_1]);
        $own = [
            'group' => [GroupCrudController::class, TestReference::GROUP_1],
            'membership' => [UserGroupCrudController::class, (string) $membership->getId()],
            'offer' => [GroupOfferCrudController::class, TestReference::GROUP_OFFER_GROUP_1_1],
        ];
        foreach ($own as $name => [$controller, $id]) {
            foreach (['detail', 'edit'] as $action) {
                $client->request('GET', self::url($action, $controller, $id));
                self::assertResponseIsSuccessful($name.' '.$action);
            }
        }
        $client->request('GET', self::url('invite', GroupCrudController::class, TestReference::GROUP_1));
        self::assertResponseIsSuccessful('group invite');
    }

    public function testPlatformAdminIsNotRestricted(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        foreach ($this->otherGroupEntities() as $name => [$controller, $id]) {
            $client->request('GET', self::url('detail', $controller, $id));
            self::assertResponseIsSuccessful($name);
        }
    }

    /**
     * Form submissions go through the entity events of the subscriber.
     */
    public function testGroupAdminCanUpdateOwnEntities(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        // own offer, unchanged
        $crawler = $client->request('GET', self::url('edit', GroupOfferCrudController::class, TestReference::GROUP_OFFER_GROUP_1_1));
        $client->submit($crawler->filter('form[name="GroupOffer"]')->form());
        self::assertResponseRedirects();

        // reassigning the offer to a group he does not administrate is refused
        $crawler = $client->request('GET', self::url('edit', GroupOfferCrudController::class, TestReference::GROUP_OFFER_GROUP_1_1));
        $form = $crawler->filter('form[name="GroupOffer"]')->form();
        $form->disableValidation();
        $form['GroupOffer[group]'] = TestReference::GROUP_7;
        $client->submit($form);
        self::assertFalse($client->getResponse()->isRedirection());
        /** @var GroupOffer $offer */
        $offer = $this->getGroupOfferRepository()->find(TestReference::GROUP_OFFER_GROUP_1_1);
        self::assertSame(TestReference::GROUP_1, (string) $offer->getGroup()->getId());

        // a group admin can step down from the admin role of his own group
        /** @var UserGroup $membership */
        $membership = $this->getUserGroupRepository()->findOneBy(['user' => TestReference::USER_16, 'group' => TestReference::GROUP_1]);
        $crawler = $client->request('GET', self::url('edit', UserGroupCrudController::class, (string) $membership->getId()));
        $form = $crawler->filter('form[name="UserGroup"]')->form();
        $form['UserGroup[membership]'] = 'member';
        $client->submit($form);
        self::assertResponseRedirects();

        // restore the admin role (the database is shared by the next test classes)
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::url('edit', UserGroupCrudController::class, (string) $membership->getId()));
        $form = $crawler->filter('form[name="UserGroup"]')->form();
        $form['UserGroup[membership]'] = 'admin';
        $client->submit($form);
        self::assertResponseRedirects();
    }
}
