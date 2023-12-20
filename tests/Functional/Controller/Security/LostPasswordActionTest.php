<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @see LostPasswordAction
 */
final class LostPasswordActionTest extends WebTestCase
{
    use ContainerRepositoryTrait;
    use KernelTrait;

    private const ROUTE = '/fr/compte/mot-de-passe-oublie';

    public function testFormSubmitUserNotFound(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', self::ROUTE);
        $form = $crawler->selectButton('lost_password_form_submit')->form();
        $client->submit($form, [
            'lost_password_form[email]' => 'usernotfound@example.com',
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'lost_password.form.success');
    }

    public function testFormSubmitSuccess(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', self::ROUTE);
        $form = $crawler->selectButton('lost_password_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[email]' => TestReference::USER_17_EMAIL,
        ]);
        self::assertResponseRedirects();
    }
}
