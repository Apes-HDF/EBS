<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\AbstractUserCrudController;
use App\Controller\Admin\UserCrudController;
use App\Entity\User;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

final class UserCrudControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    /**
     * @see UserCrudController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // list
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', UserCrudController::class));

        // list + filters
        $filters = '&filters[group]='.TestReference::GROUP_1;
        $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', UserCrudController::class).'&'.$filters);
        self::assertResponseIsSuccessful();

        // edit
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', UserCrudController::class, TestReference::USER_17));
        self::assertResponseIsSuccessful();

        // detail
        $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'detail', UserCrudController::class, TestReference::USER_16));
        self::assertResponseIsSuccessful();
    }

    /**
     * Test that the validation is applied for the phone field.
     */
    public function testValidation(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', UserCrudController::class, TestReference::USER_17));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE)->form();
        $client->submit($form, [
            $form->getName().'[phone]' => 'foobar',
        ]);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('div', 'app.controller.admin.abstract_user_crud_controller.field.phone.help');
    }

    /**
     * Nominal case for the edit action.
     */
    public function testEditAction(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'edit', UserCrudController::class, TestReference::USER_17));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(TestReference::ACTION_SAVE)->form();
        $client->submit($form, [
            $form->getName().'[phone]' => '+33610010203',
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('div', 'app.controller.admin.abstract_user_crud_controller.field.phone.help');
    }

    public function testPromoteToAdmin(): void
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', UserCrudController::class));

        $dataId = \sprintf("[data-id='%s']", TestReference::USER_17);
        self::assertSelectorTextContains($dataId, 'action.promoteToAdmin');

        $client->submit($this->getPromoteToAdminForm($crawler));
        self::assertResponseRedirects();
        self::assertEmailCount(1);
        self::assertTrue($this->getUser17()->isAdmin());
    }

    /**
     * The CSRF token is only in the action URLs of the promote forms, never in a link.
     */
    public function testPromoteToAdminTokenIsNotLeaked(): void
    {
        $client = $this->createAdminClient();
        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', UserCrudController::class));
        $token = $this->getTokenFromUrl($this->getPromoteToAdminForm($crawler)->getUri());
        self::assertNotSame('', $token);

        $links = $crawler->filter('a[href]')->each(fn (Crawler $node) => (string) $node->attr('href'));
        self::assertSame([], array_values(array_filter($links, fn (string $href) => str_contains($href, $token))));
        $forms = $crawler->filter('form[action]')->each(fn (Crawler $node) => (string) $node->attr('action'));
        foreach (array_filter($forms, fn (string $action) => str_contains($action, $token)) as $action) {
            self::assertStringContainsString('crudAction=promoteToAdmin', $action);
        }
    }

    /**
     * A GET, as with a forged link, does not promote the user, even with a valid token.
     */
    public function testPromoteToAdminGetIsRejected(): void
    {
        $client = $this->createAdminClient();
        $client->request('GET', $this->getPromoteToAdminUrl());
        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
        self::assertEmailCount(0);
        self::assertFalse($this->getUser17()->isAdmin());

        $crawler = $client->request('GET', \sprintf(TestReference::ADMIN_URL, 'index', UserCrudController::class));
        $client->request('GET', $this->getPromoteToAdminForm($crawler)->getUri());
        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
        self::assertEmailCount(0);
        self::assertFalse($this->getUser17()->isAdmin());
    }

    /**
     * A POST without token or with an invalid token does not promote the user.
     */
    public function testPromoteToAdminInvalidTokenIsRejected(): void
    {
        $client = $this->createAdminClient();

        foreach (['', '&csrfToken=', '&csrfToken=forged'] as $suffix) {
            $client->request('POST', $this->getPromoteToAdminUrl().$suffix);
            self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
            self::assertEmailCount(0);
            self::assertFalse($this->getUser17()->isAdmin());
        }
    }

    private function getPromoteToAdminUrl(): string
    {
        return \sprintf(TestReference::ADMIN_URL.'&entityId=%s', 'promoteToAdmin', UserCrudController::class, TestReference::USER_17);
    }

    /**
     * Without reboot, all requests share the kernel, so RefreshDatabaseTrait rolls back the promotion.
     */
    private function createAdminClient(): KernelBrowser
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->loginAsAdmin($client);

        return $client;
    }

    /**
     * In the index dropdown, the action is a link that submits a separate POST form.
     */
    private function getPromoteToAdminForm(Crawler $crawler): Form
    {
        $action = $crawler->filter(\sprintf("[data-id='%s'] .action-promoteToAdmin", TestReference::USER_17));
        self::assertCount(1, $action);
        $formId = (string) $action->attr('data-ea-action-form-id');
        self::assertNotSame('', $formId);
        $form = $crawler->filter('form#'.$formId)->form();
        self::assertSame('POST', $form->getMethod());

        return $form;
    }

    private function getTokenFromUrl(string $url): string
    {
        parse_str((string) parse_url($url, \PHP_URL_QUERY), $query);

        return \is_string($query[AbstractUserCrudController::PROMOTE_TO_ADMIN_CSRF_PARAMETER] ?? null) ? $query[AbstractUserCrudController::PROMOTE_TO_ADMIN_CSRF_PARAMETER] : '';
    }

    private function getUser17(): User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        return $entityManager->getRepository(User::class)->find(TestReference::USER_17) ?? throw new \LogicException('User not found.');
    }
}
