<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Util\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @see AccountCreateController
 */
final class AccountCreateActionStep1Test extends WebTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;
    use KernelTrait;

    // we only test i18 routes for the main form, all code is the same
    private const ROUTE_FR = '/fr/compte/creer-mon-compte';
    private const ROUTE_EN = '/en/account/create-my-account';

    private const NEW_USER_EMAIL = 'newuser@example.com';

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideFormSubmitValidationError(): iterable
    {
        // empty data
        yield ['', TestReference::VALIDATION_ERROR_BLANK];

        // Test that even with a different case, the validation returns an error
        // and doesn't throw a 500 error because of Doctrine constraints.
        yield [strtoupper(TestReference::USER_EMAIL), TestReference::VALIDATION_ERROR_ALREADY_USED];
    }

    /**
     * @@dataProvider provideFormSubmitValidationError
     */
    public function testFormSubmitValidationError(string $email, string $error): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', self::ROUTE_EN);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('account_create_step1_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[email]' => $email,
        ]);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', $error);
    }

    /**
     * @return iterable<array{0: string}>
     */
    public function provideFormShowSuccess(): iterable
    {
        yield ['fr', self::ROUTE_FR];
        yield ['en', self::ROUTE_EN];
    }

    /**
     * Nominal case.
     *
     * @dataProvider provideFormShowSuccess
     */
    public function testFormSubmitSuccess(string $locale, string $route): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', $route);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('account_create_step1_form_submit')->form();
        $client->submit($form, [
            $form->getName().'[email]' => $locale.self::NEW_USER_EMAIL,
        ]);

        // user was created
        $container = $client->getContainer();
        /** @var UserRepository $userRepo */
        $userRepo = $container->get(UserRepository::class);
        $user = $userRepo->findOneByEmail($locale.self::NEW_USER_EMAIL);
        self::assertInstanceOf(User::class, $user);
        self::assertNotEmpty($user->getConfirmationToken());
        self::assertNotEmpty($user->getConfirmationExpiresAt());

        // an email was sent with the confirmation link
        self::assertEmailCount(1);
        $emailMessage = self::getMailerMessage();
        self::assertInstanceOf(RawMessage::class, $emailMessage);
        self::assertEmailHtmlBodyContains($emailMessage, $user->getConfirmationToken());

        // then continue and redirect
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
