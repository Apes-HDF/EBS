<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Account;

use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

/**
 * @see ChangeLoginAction
 */
final class ChangeLoginActionTest extends WebTestCase
{
    use KernelTrait;
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const ROUTE = '/fr/mon-compte';
    private const NEW_EMAIL = 'test@domain.com';

    /**
     * The email of the account must not change before the new address is confirmed:
     * a confirmation link is sent to the new address instead.
     */
    public function testChangeLoginSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $crawler = $client->request('GET', self::ROUTE.'/mon-email');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('change_login_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[email][first]' => self::NEW_EMAIL,
            $form->getName().'[email][second]' => self::NEW_EMAIL,
        ]);

        self::assertEmailCount(1);
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email);
        self::assertEmailAddressContains($email, 'To', self::NEW_EMAIL);

        self::assertResponseRedirects(self::ROUTE);
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'app.controller.user.account.change_login_action.flash.success');

        $this->fixDoctrine();
        $user = $this->getUserRepository()->get(TestReference::USER_17);
        self::assertSame(TestReference::USER_17_EMAIL, $user->getEmail());
        self::assertNull($this->getUserRepository()->findOneByEmail(self::NEW_EMAIL));
    }

    public function testEmailAlreadyUsedWarning(): void
    {
        $client = self::createClient();
        $this->loginAsSarah($client);
        $crawler = $client->request('GET', self::ROUTE.'/mon-email');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('change_login_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[email][first]' => TestReference::ADMIN_EMAIL,
            $form->getName().'[email][second]' => TestReference::ADMIN_EMAIL,
        ]);
        self::assertResponseIsUnprocessable();
        self::assertEmailCount(0);
    }
}
