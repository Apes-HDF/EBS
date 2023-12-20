<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\ServiceRequest;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ServiceRequestRepositoryTest extends KernelTestCase
{
    use ContainerRepositoryTrait;
    use RefreshDatabaseTrait;

    private const COUNT = TestReference::SERVICE_REQUEST_COUNT;

    public function testServiceRequestRepository(): void
    {
        self::bootKernel();
        $repo = $this->getServiceRequestRepository();

        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);

        $loic = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);
        $camille = $this->getUserRepository()->get(TestReference::ADMIN_CAMILLE);
        $product = $this->getProductRepository()->get(TestReference::OBJECT_LOIC_1);

        $serviceRequest = new ServiceRequest();
        $serviceRequest->setProduct($product);
        $serviceRequest->setOwner($loic);
        $serviceRequest->setRecipient($camille);
        $serviceRequest->setStartAt(new \DateTimeImmutable('tomorrow'));
        $serviceRequest->setEndAt(new \DateTimeImmutable('+1 week'));

        $repo->save($serviceRequest, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT + 1, $count);

        $repo->remove($serviceRequest, true);
        $count = $repo->count([]);
        self::assertSame(self::COUNT, $count);
    }
}
