<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\ServiceRequest;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Specific file for the "finalize" transition.
 *
 * @see ServiceRequestStatusWorkflowController
 */
final class ServiceRequestStatusWorkflowControllerFinalizeTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/service/';
    private const ROUTE_OK = self::ROUTE.TestReference::SERVICE_REQUEST_2.'/conversation';

    private const FLASH_SUCCESS = 'app.controller.user.service_request.service_request_status_workflow_controller.flash';

    /**
     * Nominal workflow: confirmed to finalize.
     */
    public function testTransitionFinalizeSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsKevin($client);
        $crawler = $client->request('GET', self::ROUTE_OK);
        $form = $crawler->selectButton('templates.pages.account.conversation.link.finalize')->form();
        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS.'.object.finalize');

        // can't apply the same transition once again
        $client->submit($form);
        self::assertResponseIsUnprocessable();
    }
}
