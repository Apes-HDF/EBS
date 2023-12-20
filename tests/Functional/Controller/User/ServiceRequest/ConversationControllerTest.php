<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\ServiceRequest;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see ConversationController
 */
final class ConversationControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/service/';

    private const ROUTE_404 = self::ROUTE.TestReference::UUID_404.'/conversation';
    private const ROUTE_OK = self::ROUTE.TestReference::SERVICE_REQUEST_1.'/conversation';

    private const FORM_ID = 'new_message';
    private const FLASH_SUCCESS = 'app.controller.user.service_request.conversation_controller.flash.success';

    /**
     * Service request not found.
     */
    public function testNotFoundException(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('GET', self::ROUTE_404);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Access denied to other users.
     */
    public function testAccessDeniedException(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::ROUTE_OK);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Nominal case (owner).
     */
    public function testFormOwnerSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $crawler = $client->request('GET', self::ROUTE_OK);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::FORM_ID.'_submit')->form();
        $client->submit($form, [
            $form->getName().'[message]' => 'Oui bien sûr ! Je regarde pour la date et je vous confirme ça.',
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS);
    }

    /**
     * Nominal case (recipient).
     */
    public function testFormRecipientSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', self::ROUTE_OK);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::FORM_ID.'_submit')->form();
        $client->submit($form, [
            $form->getName().'[message]' => "Ok merci. J'attends donc votre confirmation..",
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS);
    }
}
