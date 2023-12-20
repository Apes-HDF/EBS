<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\ServiceRequest;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Specific file for the "refuse" transition.
 *
 * @see ServiceRequestStatusWorkflowController
 */
final class ServiceRequestStatusWorkflowControllerRefuseTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/service/';
    private const FLASH_SUCCESS = 'app.controller.user.service_request.service_request_status_workflow_controller.flash';

    /**
     * Nominal workflow: accept, confirm.
     */
    public function testTransitionRefuseSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::ROUTE.TestReference::SERVICE_REQUEST_1.'/conversation');
        $form = $crawler->selectButton('templates.pages.account.conversation.link.refuse')->form();
        $client->submit($form);
        self::assertEmailCount(1);
        self::assertNotificationCount(1);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS.'.object.refuse');
    }

    /**
     * Nominal workflow: confirm.
     */
    public function testTransitionRefuseOnConfirmStatusSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::ROUTE.TestReference::SERVICE_REQUEST_2.'/conversation');
        $form = $crawler->selectButton('templates.pages.account.conversation.link.refuse')->form();
        $client->submit($form);
        self::assertEmailCount(1);
        self::assertNotificationCount(1);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS.'.object.refuse');
    }
}
