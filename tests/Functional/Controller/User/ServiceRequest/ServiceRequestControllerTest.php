<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\ServiceRequest;

use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see ServiceRequestController
 */
final class ServiceRequestControllerTest extends WebTestCase
{
    use KernelTrait;
    use RefreshDatabaseTrait;

    private const ROUTE = '/fr/mon-compte/nouvelle-demande-de-service/';
    private const ROUTE_OK = self::ROUTE.TestReference::OBJECT_LOIC_1;
    private const ROUTE_404 = self::ROUTE.TestReference::UUID_404;
    private const FORM_ID = 'create_service_request';
    private const FLASH_SUCCESS = 'loan.new_action.form.success';

    /**
     * Product not found.
     */
    public function testNotFoundException(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('GET', self::ROUTE_404);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Nominal case.
     */
    public function testFormSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $crawler = $client->request('GET', self::ROUTE_OK);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::FORM_ID.'_submit')->form();
        $date = new \DateTimeImmutable('+ 1 month');
        $client->submit($form, [
            $form->getName().'[message]' => 'Bonjour. Je voudrais emprunter votre superbe vélo Fuji Jari 2.5. 🙂',
            $form->getName().'[startAt]' => $date->format('Y-m-d'),
            $form->getName().'[endAt]' => $date->modify('+1 week')->format('Y-m-d'),
        ]);
        self::assertEmailCount(1);
        self::assertNotificationCount(1);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', self::FLASH_SUCCESS);
    }

    /**
     * Wrong start and end dates passed as get arguments, they are simply ignored.
     */
    public function testFormWrongDateSuccess(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('GET', self::ROUTE_OK.'?startAt=foo&endAt=bar');
        self::assertResponseIsSuccessful();
    }

    /**
     * Invalid uuid, we should have a 404, not a 500.
     */
    public function testFormNotFoundOnInvalidUuid(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('GET', self::ROUTE_OK.'-nokuuid');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Validation error: ProductAvailabilityNoOverlapValidator. In the fixtures,
     * this object already has an ongoing loan that starts tomorrow (relative date).
     *
     * @see ProductAvailabilityNoOverlapValidator
     */
    public function testFormProductAvailabilityNoOverlapValidationError(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $crawler = $client->request('GET', self::ROUTE_OK);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton(self::FORM_ID.'_submit')->form();
        $date = new \DateTimeImmutable('today');
        $client->submit($form, [
            $form->getName().'[startAt]' => $date->format('Y-m-d'),
            $form->getName().'[endAt]' => $date->modify('+1 week')->format('Y-m-d'),
        ]);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'validator.product.productavailabilitynooverlap');
    }
}
