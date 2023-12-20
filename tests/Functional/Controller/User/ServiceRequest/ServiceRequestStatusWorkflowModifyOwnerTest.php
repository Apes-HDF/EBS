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
final class ServiceRequestStatusWorkflowModifyOwnerTest extends WebTestCase
{
    use KernelTrait;
    use ReloadDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/service/';

    private const ROUTE_OK = self::ROUTE.TestReference::SERVICE_REQUEST_1.'/conversation';

    private const MODIFY_CONFIRM_BUTTON = 'templates.pages.account.conversation.modal.edit.save';

    private const FLASH_SUCCESS = 'app.controller.user.service_request.service_request_status_workflow_controller.flash';

    /**
     * Validation errors when choosing dates overlaping with the product unavailabilities.
     */
    public function testTransitionValidationError(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // 1. modify dates (owner)
        $crawler = $client->request('GET', self::ROUTE_OK);
        $form = $crawler->selectButton(self::MODIFY_CONFIRM_BUTTON)->form();
        $date = new \DateTimeImmutable('+ 2 weeks'); // @see fixtures product_availability_user_1
        $client->submit($form, [
            $form->getName().'[startAt]' => $date->format('Y-m-d'),
            $form->getName().'[endAt]' => $date->modify('+1 week')->format('Y-m-d'),
        ]);
        self::assertEmailCount(0);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'validator.product.productavailabilitynooverlap');
    }

    /**
     * Nominal case.
     */
    public function testTransitionsSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // 1. modify dates (owner)
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
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS.'.object.modify_owner');
    }
}
