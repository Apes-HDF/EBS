<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\String\ByteString;

/**
 * @see AccountCreateController
 */
final class AccountCreateActionStep2PlaceTest extends WebTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;
    use KernelTrait;

    private const ROUTE = '/fr/compte/creer-mon-compte-etape-2/';

    public function testUserConfirmationTokenExpiredException(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE.TestReference::USER_13_CONFIRMATION_TOKEN);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'app.controller.security.account_create_controller.step2.user_confirmation_token_expired.warning');
    }

    public function testFormSubmitPlaceSuccess(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', self::ROUTE.TestReference::USER_12_CONFIRMATION_TOKEN);
        $form = $crawler->selectButton('account_create_step2_form_submit')->form();

        $password = ByteString::fromRandom(13);
        $client->submit($form, [
            $form->getName().'[type]' => 'place',
            $form->getName().'[name]' => 'My Association',
            $form->getName().'[plainPassword][first]' => $password,
            $form->getName().'[plainPassword][second]' => $password,
            $form->getName().'[gdpr]' => 1,
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
