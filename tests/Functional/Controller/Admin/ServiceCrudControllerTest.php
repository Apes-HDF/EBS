<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\ServiceCrudController;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ServiceCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    /**
     * @see ServiceCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', ServiceCrudController::class));
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', ServiceCrudController::class, TestReference::SERVICE_LOIC_1));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', ServiceCrudController::class, TestReference::SERVICE_LOIC_1));
        self::assertResponseIsSuccessful();

        // new
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'new', ServiceCrudController::class));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE_AND_RETURN)->form();
        $client->submit($form, [
            $form->getName().'[name]' => 'Object public',
            $form->getName().'[visibility]' => 'public',
            $form->getName().'[status]' => 'active',
            $form->getName().'[owner]' => TestReference::ADMIN_LOIC,
            $form->getName().'[description]' => 'very nice object',
            $form->getName().'[duration]' => '1 hour',
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
