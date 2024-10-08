<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\AbstractProductCrudController;
use App\Controller\Admin\ObjectCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ObjectCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const ACTION_ACTIVATE = 'action.activate';
    private const ACTION_ONBREAK = 'action.onBreak';

    /**
     * @see AbstractProductCrudController::changeStatus()
     */
    public function testChangeStatus(): void
    {
        $client = self::createClient();
        $client->followRedirects();
        $this->loginAsAdmin($client);
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', ObjectCrudController::class));
        $client->clickLink(self::ACTION_ACTIVATE);
        self::assertResponseIsSuccessful();
        $client->clickLink(self::ACTION_ONBREAK);
        self::assertResponseIsSuccessful();
    }

    public function testAvailabilityProductButton(): void
    {
        $client = self::createClient();
        $client->followRedirects();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', ObjectCrudController::class, TestReference::OBJECT_LOIC_1));
        $link = $crawler->selectLink('action.availability')->link();
        self::assertSame('http://localhost/admin?crudAction=linkToProductAvailabilityPage&crudControllerFqcn=App%5CController%5CAdmin%5CObjectCrudController&entityId='.TestReference::OBJECT_LOIC_1.'&referrer=?crudAction%3Ddetail%26crudControllerFqcn%3DApp%255CController%255CAdmin%255CObjectCrudController%26entityId%3D'.TestReference::OBJECT_LOIC_1, $client->click($link)->getUri());
    }

    /**
     * @see ObjectCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list+filter
        $filters = 'filters[enabled]=1&filters[id]='.TestReference::OBJECT_LOIC_1;
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', ObjectCrudController::class.'&'.$filters));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', ObjectCrudController::class, TestReference::OBJECT_LOIC_1));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', ObjectCrudController::class, TestReference::OBJECT_LOIC_1));
        self::assertResponseIsSuccessful();

        // new
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'new', ObjectCrudController::class));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE_AND_RETURN)->form();
        $client->submit($form, [
            $form->getName().'[name]' => 'Object public',
            $form->getName().'[status]' => 'paused',
            $form->getName().'[visibility]' => 'public',
            $form->getName().'[owner]' => TestReference::ADMIN_LOIC,
            $form->getName().'[description]' => 'very nice object',
            $form->getName().'[age]' => 'some age ago',
            $form->getName().'[deposit]' => 300,
            $form->getName().'[currency]' => 'EUR',
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
