<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Test\KernelTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * @see AddressController
 */
final class AddressControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/mon-adresse/etape-';
    private const ROUTE_STEP1 = self::ROUTE.'1';
    private const ROUTE_STEP2 = self::ROUTE.'2';

    private const STEP1_TEXT = 'address.step1_action.title';
    private const STEP2_TEXT = 'address.step2_action.confirm_title';
    private const STEP2_FLASH = 'address.step2_action.form.success';
    private const STEP2_ERROR = 'app.form.type.user.address_step2_form_type.addresses.not_null';

    private const STEP1_FORM_ID = 'address_step1_form';
    private const STEP2_FORM_ID = 'address_step2_form';

    /**
     * We try to validate the step 2 without selecting an address. Non regression
     * test for #554 in historic repo.
     */
    public function testStepsFormNoAddressValidationError(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::ROUTE_STEP1);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::STEP1_FORM_ID.'_submit')->form();
        $client->submit($form);
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::STEP2_TEXT);

        // Submit step 2 without selecting an address
        $form = $crawler->selectButton(self::STEP2_FORM_ID.'_submit')->form();

        $client->submit($form);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', self::STEP2_ERROR);
    }

    /**
     * User who already have an associated address.
     */
    public function testStepsFormSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::ROUTE_STEP1);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::STEP1_FORM_ID.'_submit')->form();
        $client->submit($form);
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::STEP2_TEXT);

        // step 2, select first address
        $form = $crawler->selectButton(self::STEP2_FORM_ID.'_submit')->form();
        /** @var ChoiceFormField $field */
        $field = $form[$form->getName().'[addresses]'];
        $field->select('0'); // first choice

        $client->submit($form);
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::STEP2_FLASH);
    }

    /**
     * User without address yet.
     */
    public function testStep1FormNoAddressSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        $crawler = $client->request('GET', self::ROUTE_STEP1);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::STEP1_FORM_ID.'_submit')->form();
        $client->submit($form, [
            $form->getName().'[address]' => '82 rue Winston Churchill',
            $form->getName().'[addressSupplement]' => '3ème étage',
            $form->getName().'[postalCode]' => '59160',
            $form->getName().'[locality]' => 'Lille',
        ]);
        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::STEP2_TEXT);

        // step 2, select first address
        $form = $crawler->selectButton(self::STEP2_FORM_ID.'_submit')->form();
        /** @var ChoiceFormField $field */
        $field = $form[$form->getName().'[addresses]'];
        $field->select('0'); // first choice

        $client->submit($form);
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::STEP2_FLASH);
    }

    /**
     * No address is found with the user input. We stay on the step1 page.
     */
    public function testStep1FormFailure(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);

        $crawler = $client->request('GET', self::ROUTE_STEP1);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::STEP1_FORM_ID.'_submit')->form();
        $client->submit($form, [
            $form->getName().'[address]' => 'ez',
            $form->getName().'[addressSupplement]' => '000',
            $form->getName().'[postalCode]' => '000',
            $form->getName().'[locality]' => '000',
        ]);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'address.step1_action.no_address.warning');
    }

    /**
     * Step1 form submitted without entering data.
     */
    public function testStep1FormNothingFilledFailure(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);

        $crawler = $client->request('GET', self::ROUTE_STEP1);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::STEP1_FORM_ID.'_submit')->form();
        $client->submit($form);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'This value should not be blank');
    }

    /**
     * Step2 direct access is forbidden.
     */
    public function testStep2DirectAccessFailure(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('GET', self::ROUTE_STEP2);
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::STEP1_TEXT);
    }
}
