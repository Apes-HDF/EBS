<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Product;
use App\Entity\ProductAvailability;
use App\Entity\ServiceRequest;
use App\Enum\Product\ProductAvailabilityType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ProductAvailibilityTest extends TestCase
{
    public function testProductAvailibility(): void
    {
        $productAvailability = new ProductAvailability();
        $id = Uuid::v6();
        self::assertSame($id, $productAvailability->setId($id)->getId());

        $product = new Product();
        self::assertSame($product, $productAvailability->setProduct($product)->getProduct());

        $serviceRequest = new ServiceRequest();
        self::assertSame($serviceRequest, $productAvailability->setServiceRequest($serviceRequest)->getServiceRequest());

        self::assertSame(ProductAvailabilityType::OWNER, $productAvailability->setType(ProductAvailabilityType::OWNER)->getType());

        $product->setName('foobar');
        $today = date_create_immutable('2023-02-09');
        $productAvailability->setStartAt($today)->setEndAt($today);
        self::assertSame('foobar / 2023-02-09 / 2023-02-09', (string) $productAvailability);
    }
}
