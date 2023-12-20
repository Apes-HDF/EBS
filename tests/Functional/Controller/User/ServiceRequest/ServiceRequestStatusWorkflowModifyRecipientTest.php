<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\ServiceRequest;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see ServiceRequestStatusWorkflowController
 */
final class ServiceRequestStatusWorkflowModifyRecipientTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE_OK = TestReference::SERVICE_REQUEST_BASE_ROUTE.TestReference::SERVICE_REQUEST_4.'/conversation';

    private const MODIFY_CONFIRM_BUTTON = 'templates.pages.account.conversation.modal.edit.save_owner';

    /**
     * The recipient modifies the service request dates.
     */
    public function testTransitionsSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', self::ROUTE_OK);
        $form = $crawler->selectButton(self::MODIFY_CONFIRM_BUTTON)->form();
        $date = new \DateTimeImmutable('+ 3 days');
        $client->submit($form, [
            $form->getName().'[startAt]' => $date->format('Y-m-d'),
            $form->getName().'[endAt]' => $date->modify('+3 days')->format('Y-m-d'),
        ]);
        self::assertEmailCount(1);
        self::assertNotificationCount(1);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', TestReference::SERVICE_REQUEST_WORKFLOW_FLASH_SUCCESS.'.object.modify_recipient');
    }
}
