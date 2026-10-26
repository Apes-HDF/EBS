<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\Product;
use App\Entity\ProductAvailability;
use App\Entity\ServiceRequest;
use App\Enum\Product\ProductAvailabilityMode;
use App\Enum\Product\ProductAvailabilityType;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Date ranges entered by users are bounded, so that a single row cannot make
 * the calendar of a product expand millions of days.
 */
final class DateRangeBoundsTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use KernelTrait;
    use ContainerRepositoryTrait;

    private const AVAILABILITY_ROUTE = '/fr/mon-compte/mes-produits/'.TestReference::OBJECT_LOIC_1.'/disponibilites';
    private const SERVICE_REQUEST_ROUTE = '/fr/mon-compte/nouvelle-demande-de-service/'.TestReference::OBJECT_LOIC_1;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideInvalidRanges(): iterable
    {
        $today = CarbonImmutable::today();
        yield 'far future' => [$today->addYears(5)->format('Y-m-d'), $today->addYears(1000)->format('Y-m-d')];
        yield 'end before start' => [$today->addDays(10)->format('Y-m-d'), $today->addDays(5)->format('Y-m-d')];
        yield 'too long' => ['0001-01-01', $today->addDays(10)->format('Y-m-d')];
    }

    /**
     * @dataProvider provideInvalidRanges
     */
    public function testProductUnavailabilityRangeIsBounded(string $startAt, string $endAt): void
    {
        $client = self::createClient();
        // loic owns the product
        $this->loginAsAdmin($client);
        $count = $this->getProductAvailabilityRepository()->count([]);
        $crawler = $client->request('GET', self::AVAILABILITY_ROUTE);
        $form = $crawler->selectButton('create_product_availability_submit')->form();
        $client->submit($form, [
            $form->getName().'[startAt]' => $startAt,
            $form->getName().'[endAt]' => $endAt,
        ]);
        // the form is displayed again with the date range error
        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('validator.date_range.', (string) $client->getResponse()->getContent());
        self::assertSame($count, $this->getProductAvailabilityRepository()->count([]));
    }

    /**
     * @dataProvider provideInvalidRanges
     */
    public function testServiceRequestRangeIsBounded(string $startAt, string $endAt): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $count = $this->getServiceRequestRepository()->count([]);
        $crawler = $client->request('GET', self::SERVICE_REQUEST_ROUTE);
        $form = $crawler->selectButton('create_service_request_submit')->form();
        $client->submit($form, [
            $form->getName().'[message]' => 'Bonjour',
            $form->getName().'[startAt]' => $startAt,
            $form->getName().'[endAt]' => $endAt,
        ]);
        // the form is displayed again with the date range error
        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('validator.date_range.', (string) $client->getResponse()->getContent());
        self::assertSame($count, $this->getServiceRequestRepository()->count([]));
    }

    /**
     * A range stored before the fix only expands within the booking horizon.
     */
    public function testStoredOutlierIsNotFullyExpanded(): void
    {
        self::bootKernel();
        /** @var Product $product */
        $product = $this->getProductRepository()->find(TestReference::OBJECT_LOIC_1);
        $outlier = (new ProductAvailability())
            ->setProduct($product)
            ->setMode(ProductAvailabilityMode::UNAVAILABLE)
            ->setType(ProductAvailabilityType::OWNER)
            ->setStartAt(new \DateTimeImmutable('2000-01-01'))
            ->setEndAt(new \DateTimeImmutable('5000-01-01'));
        $product->addAvailability($outlier);

        $days = $product->getUnavailabilities();
        self::assertLessThanOrEqual(ProductAvailability::MAX_DAYS + 1, \count($days));
        self::assertContains(CarbonImmutable::today()->format('Y-m-d'), $days);
    }

    /**
     * Refused dates of the modify transition must not be saved by the conversation
     * page the invalid form is forwarded to.
     */
    public function testModifyRefusedDatesAreNotSaved(): void
    {
        $client = self::createClient();
        // loic owns the product of the service request 1
        $this->loginAsAdmin($client);
        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $this->getServiceRequestRepository()->find(TestReference::SERVICE_REQUEST_1);
        $dates = [$serviceRequest->getStartAt()->format('Y-m-d'), $serviceRequest->getEndAt()->format('Y-m-d')];

        $crawler = $client->request('GET', '/fr/mon-compte/service/'.TestReference::SERVICE_REQUEST_1.'/conversation');
        $form = $crawler->selectButton('templates.pages.account.conversation.modal.edit.save')->form();
        $client->submit($form, [
            $form->getName().'[startAt]' => '0001-01-01',
            $form->getName().'[endAt]' => CarbonImmutable::today()->addYears(1000)->format('Y-m-d'),
        ]);
        self::assertResponseIsUnprocessable();
        self::assertSelectorTextContains('body', 'validator.date_range.');

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        /** @var ServiceRequest $serviceRequest */
        $serviceRequest = $this->getServiceRequestRepository()->find(TestReference::SERVICE_REQUEST_1);
        self::assertSame($dates, [$serviceRequest->getStartAt()->format('Y-m-d'), $serviceRequest->getEndAt()->format('Y-m-d')]);
    }
}
