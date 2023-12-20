<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\Group;
use App\Entity\Product;
use App\Entity\ProductAvailability;
use App\Entity\ServiceRequest;
use App\Enum\Product\ProductAvailabilityMode;
use App\Enum\Product\ProductAvailabilityType;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ProductTest extends TestCase
{
    public function testProduct(): void
    {
        $category = new Category();
        $product = (new Product())
            ->setCategory($category);
        $id = Uuid::v6();
        self::assertSame($id, $product->setId($id)->getId());
        self::assertSame('prd1', $product->setSlug('prd1')->getSlug());
        self::assertSame('age', $product->setAge('age')->getAge());
        self::assertNull($product->getFirstImage());
        self::assertNull($product->getSubCategory());
        self::assertSame($category, $product->getMainCategory());
        self::assertSame(['img1.png'], $product->setImages(['img1.png'])->getImages());
        self::assertSame('2 jours', $product->setPreferredLoanDuration('2 jours')->getPreferredLoanDuration());
        $product->setPaused();
        self::assertTrue($product->setActive()->getStatus()->isActive());

        self::assertFalse($product->hasServiceRequests());
        $serviceRequestCollection = new ArrayCollection();
        $sr = new ServiceRequest();
        $serviceRequestCollection->add($sr);
        $product->setServiceRequests($serviceRequestCollection);
        self::assertSame($serviceRequestCollection, $product->getServiceRequests());
        self::assertTrue($product->hasServiceRequests());

        $availabilities = new ArrayCollection();
        $productAvailability = new ProductAvailability();
        $availabilities->add($productAvailability);
        $product->setAvailabilities($availabilities);
        self::assertCount(1, $product->getAvailabilities());

        self::assertCount(0, $product->getGroups());
        $group = new Group();
        $product->addGroup($group);
        self::assertCount(1, $product->getGroups());
        $product->removeGroup($group);
        self::assertCount(0, $product->getGroups());
    }

    public function testProductObject(): void
    {
        $product = new Product();
        self::assertSame(100, $product->setDeposit(100)->getDeposit());
        self::assertSame('EUR', $product->setCurrency('EUR')->getCurrency());
    }

    public function testGetUnavailabilities(): void
    {
        $product = new Product();
        self::assertEmpty($product->getUnavailabilities(null));

        $today = new \DateTimeImmutable('today');
        $tomorrow = new \DateTimeImmutable('tomorrow');
        $pa = (new ProductAvailability())
            ->setProduct($product)
            ->setType(ProductAvailabilityType::OWNER)
            ->setMode(ProductAvailabilityMode::UNAVAILABLE)
            ->setStartAt($today)
            ->setEndAt($tomorrow)
        ;
        $product->addAvailability($pa);

        self::assertCount(1, $product->getAvailabilities());
        self::assertCount(2, $product->getUnavailabilities()); // 2 days
        self::assertSame([
            $today->format('Y-m-d'),
            $tomorrow->format('Y-m-d'),
        ], $product->getUnavailabilities(null));

        // now assign the sr to the product unavailability
        $sr = (new ServiceRequest())->setProduct($product);
        $pa->setServiceRequest($sr);

        self::assertCount(2, $product->getUnavailabilities());
        // days are exclude now because the service request is not taken in account
        self::assertCount(0, $product->getUnavailabilities($sr));

        $product->removeAvailability($pa);
        self::assertCount(0, $product->getAvailabilities());
    }

    /**
     * Non regression test for issue #395 in historic repo.
     */
    public function testBug395(): void
    {
        $product = new Product();
        $product->setImages([
            'image1',
            'image2',
            'image3',
        ]);

        $product->deleteImage('image2');
        self::assertSame(['image1', 'image3'], $product->getImages());
    }

    /**
     * Non regression test for #463 in historic repo.
     */
    public function testSetImages(): void
    {
        $product = new Product();
        $product->setImages([
            '',
            null,
            'image3',
        ]);

        self::assertSame(['image3'], $product->getImages());
    }
}
