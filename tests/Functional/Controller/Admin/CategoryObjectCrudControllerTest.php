<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\CategoryObjectCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FileFormField;

/**
 * @see CategoryObjectCrudController
 */
final class CategoryObjectCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const SAVE_AND_CONTINUE_BUTTON_NAME = 'ea[newForm][btn]';

    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', CategoryObjectCrudController::class));
        self::assertResponseIsSuccessful();

        // edit
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', CategoryObjectCrudController::class, TestReference::CATEGORY_OBJECT_1));
        self::assertResponseIsSuccessful();

        // upload
        $form = $crawler->selectButton(self::SAVE_AND_CONTINUE_BUTTON_NAME)->form();
        $image = realpath(__DIR__.'/../../../Fixtures/images/apes.png');
        /** @var FileFormField $fileFormField */
        $fileFormField = $form[$form->getName().'[image][file]'];
        $fileFormField->upload((string) $image);

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        // delete file
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', CategoryObjectCrudController::class, TestReference::CATEGORY_OBJECT_1));
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton(self::SAVE_AND_CONTINUE_BUTTON_NAME)->form();
        /** @var ChoiceFormField $choiceFormField */
        $choiceFormField = $form[$form->getName().'[image][delete]'];
        $choiceFormField->tick();
        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', CategoryObjectCrudController::class, TestReference::CATEGORY_OBJECT_1));
        self::assertResponseIsSuccessful();

        // new
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'new', CategoryObjectCrudController::class));
        self::assertResponseIsSuccessful();

        // move up
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'moveUp', CategoryObjectCrudController::class, TestReference::CATEGORY_OBJECT_1));
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        // move down
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'moveDown', CategoryObjectCrudController::class, TestReference::CATEGORY_OBJECT_1));
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
