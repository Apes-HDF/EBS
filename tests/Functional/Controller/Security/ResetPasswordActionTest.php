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
 * @see ResetPasswordAction
 */
final class ResetPasswordActionTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use ContainerRepositoryTrait;
    use KernelTrait;

    private const ROUTE = '/fr/compte/reinitialisation-mot-de-passe/';

    public function testUserNotFoundException(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE.'foobar');
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'reset_password.user_not_found.exception');
    }

    public function testUserLostPasswordTokenExpiredException(): void
    {
        $client = self::createClient();
        $client->request('GET', self::ROUTE.TestReference::USER_15_LOST_PASSWORD_TOKEN);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'reset_password.user_lostpassword_token_expired.exception');
    }

    public function testFormSubmitSucess(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', self::ROUTE.TestReference::USER_14_LOST_PASSWORD_TOKEN);
        $form = $crawler->selectButton('reset_password_form_submit')->form();

        $newPassword = ByteString::fromRandom(13); // min=8 @see UserManager
        $client->submit($form, [
            $form->getName().'[password][first]' => $newPassword,
            $form->getName().'[password][second]' => $newPassword,
        ]);
        self::assertResponseRedirects();
    }
}
