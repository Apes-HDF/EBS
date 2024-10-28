<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\MenuItemFooterCrudController;
use App\Controller\Admin\MenuItemMenuSocialNetwordFooterCrudController;
use App\Controller\Admin\NewMenuFooterLinkController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FooterItemCrudControllerTest extends WebTestCase
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

        $dataId = \sprintf("[data-id='%s']", TestReference::MENU_FOOTER_ITEM_FIRST);
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', MenuItemFooterCrudController::class));

        self::assertSelectorTextNotContains($dataId, 'menu.action.up_item');
        self::assertSelectorTextContains($dataId, 'menu.action.down_item');

        $downLink = $client->getCrawler()->filter($dataId.' .action-down')->link();
        $client->click($downLink);

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

        $dataId = \sprintf("[data-id='%s']", TestReference::MENU_FOOTER_ITEM_LAST);
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', MenuItemFooterCrudController::class));

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

        // list
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', MenuItemFooterCrudController::class));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', MenuItemFooterCrudController::class, TestReference::MENU_FOOTER_ITEM_FIRST));
        self::assertResponseIsSuccessful();
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', MenuItemFooterCrudController::class, TestReference::MENU_FOOTER_ITEM_LAST));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', MenuItemFooterCrudController::class, TestReference::MENU_FOOTER_ITEM_FIRST));
        self::assertResponseIsSuccessful();

        // new icon link
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'new', MenuItemMenuSocialNetwordFooterCrudController::class));
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
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'new', NewMenuFooterLinkController::class));
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
