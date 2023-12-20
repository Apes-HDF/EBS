<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Account;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see ChangePasswordAction
 */
final class ChangePasswordActionTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const ROUTE = '/fr/mon-compte';

    public function testChangePasswordSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::ROUTE.'/mon-mot-de-passe');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('templates.pages.user.account.change_password.submit')->form();
        $client->submit($form, [
            $form->getName().'[oldPassword]' => TestReference::PASSWORD_FIXTURES,
            $form->getName().'[plainPassword][first]' => 'password',
            $form->getName().'[plainPassword][second]' => 'password',
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'app.controller.user.account.change_password_action.flash.success');
    }

    public function testChangePasswordWithWrongOldPassword(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::ROUTE.'/mon-mot-de-passe');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('templates.pages.user.account.change_password.submit')->form();
        $client->submit($form, [
            $form->getName().'[oldPassword]' => 'old',
            $form->getName().'[plainPassword][first]' => 'password',
            $form->getName().'[plainPassword][second]' => 'password',
        ]);

        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'This value should be the user\'s current password.');
    }

    public function testChangePasswordWithValuesNotMatching(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::ROUTE.'/mon-mot-de-passe');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('templates.pages.user.account.change_password.submit')->form();
        $client->submit($form, [
            $form->getName().'[oldPassword]' => TestReference::PASSWORD_FIXTURES,
            $form->getName().'[plainPassword][first]' => 'password',
            $form->getName().'[plainPassword][second]' => 'newpassword',
        ]);

        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'The values do not match.');
    }

    public function testChangePasswordWithBlankValue(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::ROUTE.'/mon-mot-de-passe');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('templates.pages.user.account.change_password.submit')->form();
        $client->submit($form, [
            $form->getName().'[oldPassword]' => TestReference::PASSWORD_FIXTURES,
            $form->getName().'[plainPassword][first]' => '',
            $form->getName().'[plainPassword][second]' => '',
        ]);

        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'This value should not be blank.');
    }
}
