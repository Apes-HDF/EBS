<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\AbstractMenuItemCrudController;
use App\Controller\Admin\MenuItemCrudController;
use App\Controller\Admin\MenuItemLinkCrudController;
use App\Controller\Admin\MenuItemSocialNetworkCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MenuItemCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    /**
     * @see AbstractMenuItemCrudController::moveDownPosition()
     */
    public function testMoveDownPosition(): void
    {
        $client = self::createClient();
        $client->followRedirects();

        $this->loginAsAdmin($client);

        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', MenuItemCrudController::class));

        $dataId = sprintf("[data-id='%s']", TestReference::MENU_HEADER_ITEM_FIRST);
        self::assertSelectorTextNotContains($dataId, 'menu.action.up_item');
        self::assertSelectorTextContains($dataId, 'menu.action.down_item');

        $client->clickLink('menu.action.down_item');

        self::assertSelectorTextContains($dataId, 'menu.action.up_item');
        self::assertSelectorTextContains($dataId, 'menu.action.down_item');
    }

    /**
     * @see AbstractMenuItemCrudController::moveUpPosition()
     */
    public function testMoveUpPosition(): void
    {
        $client = self::createClient();
        $client->followRedirects();

        $this->loginAsAdmin($client);

        $client->request('GET', sprintf(TestReference::ADMIN_URL, 'index', MenuItemCrudController::class));
        $dataId = sprintf("[data-id='%s']", TestReference::MENU_HEADER_ITEM_LAST);

        self::assertSelectorTextContains($dataId, 'menu.action.up_item');
        $upLink = $client->getCrawler()->filter($dataId.' .action-up')->link();
        $client->click($upLink);

        self::assertSelectorTextContains($dataId, 'menu.action.up_item');
        self::assertSelectorTextContains($dataId, 'menu.action.down_item');
    }

    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list + filter
        $filters = 'filters[mediaType]=facebook';
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&'.$filters, 'index', MenuItemCrudController::class));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', MenuItemCrudController::class, TestReference::MENU_HEADER_ITEM_FIRST));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', MenuItemCrudController::class, TestReference::MENU_HEADER_ITEM_FIRST));
        self::assertResponseIsSuccessful();

        // new icon link
        $crawler = $client->request('GET', sprintf(TestReference::ADMIN_URL, 'new', MenuItemSocialNetworkCrudController::class));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE_AND_RETURN)->form();
        $client->submit($form, [
            $form->getName().'[mediaType]' => 'facebook',
            $form->getName().'[link]' => '/bar',
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        // new text link
        $crawler = $client->request('GET', sprintf(TestReference::ADMIN_URL, 'new', MenuItemLinkCrudController::class));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE_AND_RETURN)->form();
        $client->submit($form, [
            $form->getName().'[name]' => 'foo',
            $form->getName().'[link]' => '/bar',
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
