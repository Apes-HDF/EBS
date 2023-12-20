<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\ParametersController;
use App\Test\KernelTrait;
use App\Tests\Functional\ChoiceFormFieldTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\FormField;

final class ParametersControllerTest extends WebTestCase
{
    use KernelTrait;

    use RefreshDatabaseTrait;

    use ChoiceFormFieldTrait;

    /**
     * @see ParametersController
     */
    public function testController(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);

        // form
        $crawler = $client->request('GET', sprintf(TestReference::ADMIN_URL_CUSTOM_CONTROLLER, 'admin_parameters'));
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('parameters_form_submit')->form();

        self::assertSame(5, $crawler->filter('input:checked')->count());

        /** @var FormField $notificationsSenderEmailField */
        $notificationsSenderEmailField = $form->get('parameters_form[notificationsSenderEmail]');

        /** @var FormField $notificationsSenderName */
        $notificationsSenderName = $form->get('parameters_form[notificationsSenderName]');

        /** @var FormField $groupsCreationMode */
        $groupsCreationMode = $form->get('parameters_form[groupsCreationMode]');

        /** @var FormField $contactFormEmail */
        $contactFormEmail = $form->get('parameters_form[contactFormEmail]');

        self::assertSame('info@example.com', $notificationsSenderEmailField->getValue());
        self::assertSame('Contact', $notificationsSenderName->getValue());
        self::assertSame('all', $groupsCreationMode->getValue());
        self::assertSame('info@example.com', $contactFormEmail->getValue());

        $this->tick($form, $form->getName().'[contactFormEnabled]', false)
            ->tick($form, $form->getName().'[groupsEnabled]', false)
            ->tick($form, $form->getName().'[groupsPaying]', false)
            ->tick($form, $form->getName().'[confidentialityConversationAdminAccess]', false);

        $client->submit($form, [
            $form->getName().'[notificationsSenderEmail]' => 'test@example.com',
            $form->getName().'[notificationsSenderName]' => 'Contact test',
            $form->getName().'[contactFormEmail]' => 'test@example.com',
            $form->getName().'[groupsCreationMode]' => 'only_admin',
        ]);

        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertResponseIsSuccessful();

        self::assertSame(1, $crawler->filter('input:checked')->count());

        $form = $crawler->selectButton('parameters_form_submit')->form();
        /** @var FormField $groupsCreationModeField */
        $groupsCreationModeField = $form->get('parameters_form[groupsCreationMode]');
        self::assertSame('only_admin', $groupsCreationModeField->getValue());
    }
}
