<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class SecurityControllerTest extends WebTestCase
{
    use ContainerRepositoryTrait;
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const USERNAME_FIELD = '_username';
    private const PASSWORD_FIELD = '_password';

    /**
     * @see SecurityController::login()
     */
    public function testLogin(): void
    {
        $client = self::createClient();
        $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
    }

    /**
     * @see SecurityController::logout()
     */
    public function testLogout(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('GET', '/logout');
        self::assertResponseRedirects('http://localhost/login');
    }

    /**
     * Test the login manually so we don't skip some code parts, like the checkPostAuth()
     * function of UserChecker. In all other tests we can use the login() helpers.
     */
    public function testAdminLoginSuccess(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('submit')->form();
        $client->followRedirects();
        $client->submit($form, [
            self::USERNAME_FIELD => TestReference::ADMIN_EMAIL,
            self::PASSWORD_FIELD => TestReference::PASSWORD_FIXTURES,
        ]);
        self::assertResponseIsSuccessful();
        self::assertRouteSame('admin');
    }

    public function testUserLoginSuccess(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('submit')->form();
        $client->followRedirects();
        $client->submit($form, [
            self::USERNAME_FIELD => TestReference::USER_EMAIL,
            self::PASSWORD_FIELD => TestReference::PASSWORD_FIXTURES,
        ]);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('app_user_my_account');
    }

    /**
     * Test that a user with a disabled account can't login.
     *
     * @see UserEnabledChecker
     */
    public function testLoginAccountDisabledException(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('submit')->form();
        $client->submit($form, [
            self::USERNAME_FIELD => 'user10@example.com',
            self::PASSWORD_FIELD => TestReference::PASSWORD_FIXTURES,
        ]);
        self::assertResponseRedirects('http://localhost/login');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'login.account_disabled_exception');
    }

    /**
     * Test that a user with an unconfirmed email can't login.
     *
     * @see UserEmailConfirmedChecker
     */
    public function testLoginAccountEmailNotConfirmedException(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('submit')->form();
        $client->submit($form, [
            self::USERNAME_FIELD => 'user13@example.com',
            self::PASSWORD_FIELD => TestReference::PASSWORD_FIXTURES,
        ]);
        self::assertResponseRedirects('http://localhost/login');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'login.account_email_not_confirmed_exceptio');
    }

    /**
     * Admin cannot be accessed by anonymous users.
     */
    public function testAdminSecured(): void
    {
        $client = self::createClient();
        $client->request('GET', '/admin');
        self::assertResponseRedirects('/login');
    }

    /**
     * Admin cannot be accessed by standard users.
     */
    public function testAdminSecuredDenied(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('GET', '/admin');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
