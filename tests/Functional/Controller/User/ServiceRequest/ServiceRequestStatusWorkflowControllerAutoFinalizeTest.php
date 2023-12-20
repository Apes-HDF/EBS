<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\ServiceRequest;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Specific file for the "autoFinalize" transition. The service request 3 has the
 * confirmed status but its end date is in the past, so the autoFinalize transition
 * can be applied.
 *
 * @see ConversationController
 */
final class ServiceRequestStatusWorkflowControllerAutoFinalizeTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/service/';
    private const ROUTE_OK = self::ROUTE.TestReference::SERVICE_REQUEST_3.'/conversation';

    /**
     * Nominal workflow: confirmed to finalized (system transition).
     */
    public function testTransitionFinalizeSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::ROUTE_OK);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'message.system.finalized');
    }
}
