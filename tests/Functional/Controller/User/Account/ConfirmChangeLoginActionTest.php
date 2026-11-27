<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Account;

use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

/**
 * @see ConfirmChangeLoginAction
 */
final class ConfirmChangeLoginActionTest extends WebTestCase
{
    use KernelTrait;
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const ROUTE = '/fr/mon-compte';
    private const CONFIRM_ROUTE = '/fr/compte/confirmer-mon-email/';
    private const NEW_EMAIL = 'test@domain.com';
    private const UNCONFIRMED_USER_EMAIL = 'user15@example.com';
    private const I18N_PREFIX = 'app.controller.user.account.confirm_change_login_action';

    /**
     * These tests chain several requests that write: keep the same kernel, and so
     * the same connection, to have every write rolled back by RefreshDatabaseTrait.
     */
    private function createClientWithoutReboot(): KernelBrowser
    {
        $client = self::createClient();
        $client->disableReboot();

        return $client;
    }

    /**
     * Submit the change login form as user 17 and return the confirmation link
     * found in the email sent to the new address.
     */
    private function requestChangeLogin(KernelBrowser $client, string $newEmail): string
    {
        $this->loginAsUser($client);
        $crawler = $client->request('GET', self::ROUTE.'/mon-email');
        $form = $crawler->selectButton('change_login_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[email][first]' => $newEmail,
            $form->getName().'[email][second]' => $newEmail,
        ]);
        self::assertResponseRedirects(self::ROUTE);
        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', $newEmail);
        if (preg_match('#'.self::CONFIRM_ROUTE.'([A-Za-z0-9]+)#', (string) $email->getHtmlBody(), $matches) !== 1) {
            self::fail('The confirmation link was not found in the email.');
        }

        return self::CONFIRM_ROUTE.$matches[1];
    }

    private function assertUser17Email(string $expected): void
    {
        $this->fixDoctrine();
        $user = $this->getUserRepository()->get(TestReference::USER_17);
        self::assertSame($expected, $user->getEmail());
    }

    public function testConfirmSuccess(): void
    {
        $client = $this->createClientWithoutReboot();
        $link = $this->requestChangeLogin($client, self::NEW_EMAIL);
        $this->assertUser17Email(TestReference::USER_17_EMAIL);

        $client->request('GET', $link);
        self::assertResponseRedirects(self::ROUTE);
        // the session is kept with the new identifier
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::I18N_PREFIX.'.flash.success');

        $this->assertUser17Email(self::NEW_EMAIL);
        $user = $this->getUserRepository()->get(TestReference::USER_17);
        self::assertTrue($user->isEmailConfirmed());
        self::assertNull($user->getPendingEmail());
        self::assertNull($user->getPendingEmailToken());
        self::assertNull($user->getPendingEmailExpiresAt());

        // the link can't be used twice
        $client->request('GET', $link);
        self::assertResponseRedirects(self::ROUTE);
        $client->followRedirect();
        self::assertSelectorTextContains('body', self::I18N_PREFIX.'.flash.token_invalid');
    }

    public function testConfirmInvalidToken(): void
    {
        $client = $this->createClientWithoutReboot();
        $this->requestChangeLogin($client, self::NEW_EMAIL);
        $client->request('GET', self::CONFIRM_ROUTE.'foobar');
        self::assertResponseRedirects(self::ROUTE);
        $client->followRedirect();
        self::assertSelectorTextContains('body', self::I18N_PREFIX.'.flash.token_invalid');
        $this->assertUser17Email(TestReference::USER_17_EMAIL);
    }

    public function testConfirmExpiredToken(): void
    {
        $client = $this->createClientWithoutReboot();
        $link = $this->requestChangeLogin($client, self::NEW_EMAIL);

        $this->fixDoctrine();
        $user = $this->getUserRepository()->get(TestReference::USER_17);
        $user->setPendingEmailExpiresAt(new \DateTimeImmutable('-1 minute'));
        $this->getUserRepository()->save($user, true);

        $client->request('GET', $link);
        self::assertResponseRedirects(self::ROUTE);
        $client->followRedirect();
        self::assertSelectorTextContains('body', self::I18N_PREFIX.'.flash.token_expired');
        $this->assertUser17Email(TestReference::USER_17_EMAIL);
    }

    /**
     * The link only works for the account that made the request.
     */
    public function testConfirmWithAnotherAccount(): void
    {
        $client = $this->createClientWithoutReboot();
        $link = $this->requestChangeLogin($client, self::NEW_EMAIL);

        $this->loginAsSarah($client);
        $client->request('GET', $link);
        self::assertResponseRedirects(self::ROUTE);
        $client->followRedirect();
        self::assertSelectorTextContains('body', self::I18N_PREFIX.'.flash.token_invalid');
        $this->assertUser17Email(TestReference::USER_17_EMAIL);
        self::assertNull($this->getUserRepository()->findOneByEmail(self::NEW_EMAIL));
    }

    public function testConfirmNotLoggedIn(): void
    {
        $client = $this->createClientWithoutReboot();
        $client->request('GET', self::CONFIRM_ROUTE.'foobar');
        self::assertResponseRedirects('/login');
    }

    /**
     * The form only refuses emails of confirmed accounts: an unconfirmed account
     * with the same email must also block the change.
     */
    public function testConfirmEmailUsedByUnconfirmedAccount(): void
    {
        $client = $this->createClientWithoutReboot();
        $link = $this->requestChangeLogin($client, self::UNCONFIRMED_USER_EMAIL);

        $client->request('GET', $link);
        self::assertResponseRedirects(self::ROUTE);
        $client->followRedirect();
        self::assertSelectorTextContains('body', self::I18N_PREFIX.'.flash.email_already_used');
        $this->assertUser17Email(TestReference::USER_17_EMAIL);
    }

    /**
     * The new email is taken by another account between the request and the confirmation.
     */
    public function testConfirmEmailTakenInTheMeantime(): void
    {
        $client = $this->createClientWithoutReboot();
        $link = $this->requestChangeLogin($client, self::NEW_EMAIL);

        $this->fixDoctrine();
        $sarah = $this->getUserRepository()->get(TestReference::ADMIN_SARAH);
        $sarah->setEmail(self::NEW_EMAIL);
        $this->getUserRepository()->save($sarah, true);

        $client->request('GET', $link);
        self::assertResponseRedirects(self::ROUTE);
        $client->followRedirect();
        self::assertSelectorTextContains('body', self::I18N_PREFIX.'.flash.email_already_used');
        $this->assertUser17Email(TestReference::USER_17_EMAIL);
    }
}
