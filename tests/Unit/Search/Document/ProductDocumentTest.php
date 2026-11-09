<?php

declare(strict_types=1);

namespace App\Tests\Unit\Search\Document;

use App\Entity\Address;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\Product\ProductType;
use App\Enum\User\UserType;
use App\Search\Document\GeoDocument;
use App\Search\Document\ProductDocument;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ProductDocumentTest extends TestCase
{
    private const LATITUDE = '50.6241657';
    private const LONGITUDE = '3.0809541';

    /**
     * @return iterable<string, array{0: ?UserType}>
     */
    public static function provideOwnerWithPrivateAddress(): iterable
    {
        yield 'user' => [UserType::USER];
        yield 'admin' => [UserType::ADMIN];
        yield 'no type' => [null];
    }

    /**
     * The location of an owner whose address isn't public is blurred in the index.
     *
     * @dataProvider provideOwnerWithPrivateAddress
     */
    public function testFromProductRoundsThePrivateOwnerLocation(?UserType $type): void
    {
        $document = ProductDocument::fromProduct($this->getProduct($type, $this->getAddress()));

        self::assertInstanceOf(GeoDocument::class, $document->_geo);
        self::assertSame(50.62, $document->_geo->lat);
        self::assertSame(3.08, $document->_geo->lng);
    }

    /**
     * A place has a public address: its location stays exact.
     */
    public function testFromProductKeepsThePlaceLocation(): void
    {
        $document = ProductDocument::fromProduct($this->getProduct(UserType::PLACE, $this->getAddress()));

        self::assertInstanceOf(GeoDocument::class, $document->_geo);
        self::assertSame(50.6241657, $document->_geo->lat);
        self::assertSame(3.0809541, $document->_geo->lng);
    }

    public function testFromProductWithoutAddress(): void
    {
        $document = ProductDocument::fromProduct($this->getProduct(UserType::USER, null));

        self::assertNull($document->_geo);
    }

    public function testFromAddressRoundsOnlyWhenAPrecisionIsGiven(): void
    {
        $address = $this->getAddress();

        $exact = GeoDocument::fromAddress($address);
        self::assertSame(50.6241657, $exact->lat);
        self::assertSame(3.0809541, $exact->lng);

        $blurred = GeoDocument::fromAddress($address, GeoDocument::PRIVATE_PRECISION);
        self::assertSame(50.62, $blurred->lat);
        self::assertSame(3.08, $blurred->lng);
    }

    private function getAddress(): Address
    {
        return (new Address())
            ->setLatitude(self::LATITUDE)
            ->setLongitude(self::LONGITUDE);
    }

    private function getProduct(?UserType $ownerType, ?Address $address): Product
    {
        $owner = (new User())
            ->setId(Uuid::v6())
            ->setType($ownerType)
            ->setAddress($address);

        $category = (new Category())
            ->setId(Uuid::v6())
            ->setName('Outils');

        return (new Product())
            ->setId(Uuid::v6())
            ->setType(ProductType::OBJECT)
            ->setName('Perceuse')
            ->setCategory($category)
            ->setOwner($owner);
    }
}
