<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\ServiceRequest;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see ServiceRequestStatusWorkflowController
 */
final class ServiceRequestStatusWorkflowControllerTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/service/';

    private const ROUTE_OK = self::ROUTE.TestReference::SERVICE_REQUEST_1.'/conversation';

    private const FLASH_SUCCESS = 'app.controller.user.service_request.service_request_status_workflow_controller.flash';

    /**
     * Form is posted without CSRF token.
     */
    public function testTransitionCsrfException(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('POST', self::ROUTE.TestReference::SERVICE_REQUEST_1.'/transition/accept');
        self::assertEmailCount(0);
        self::assertResponseIsUnprocessable();
    }

    /**
     * Method not allowed (post with CSRF is needed).
     */
    public function testTransitionLogicMethodNotAllowedException(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', self::ROUTE_OK.'/finalize');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Nominal workflow: accept, confirm.
     */
    public function testTransitionsSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // 1. accept (owner)
        $crawler = $client->request('GET', self::ROUTE_OK);
        $form = $crawler->selectButton('templates.pages.account.conversation.link.confirm')->form();
        $client->submit($form);
        self::assertEmailCount(1);
        self::assertNotificationCount(1);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS.'.object.accept');

        // 2. confirm (recipient)
        $this->logout($client);
        $this->loginAsUser($client);
        $crawler = $client->request('GET', self::ROUTE_OK);
        $form = $crawler->selectButton('templates.pages.account.conversation.link.confirm')->form();
        $client->submit($form);
        self::assertEmailCount(1);
        self::assertNotificationCount(1);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS.'.object.confirm');
    }
}
