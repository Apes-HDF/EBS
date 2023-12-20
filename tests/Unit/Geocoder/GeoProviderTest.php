<?php

declare(strict_types=1);

namespace App\Tests\Unit\Geocoder;

use App\Geocoder\Adapter\NominatimToAddressAdapter;
use App\Geocoder\GeoProvider;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Coordinates;
use Geocoder\Provider\Nominatim\Model\NominatimAddress;
use Geocoder\Provider\Provider;
use PHPUnit\Framework\TestCase;

/**
 * Complete code coverage because we have mocked the geocoder HTTP calls to avoid
 * making the CI unstable.
 */
final class GeoProviderTest extends TestCase
{
    public function testServerException(): void
    {
        $nominatimGeocoder = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nominatimGeocoder->method('geocodeQuery')
            ->willThrowException(new InvalidServerResponse());

        $geoProvider = new GeoProvider($nominatimGeocoder, new NominatimToAddressAdapter());
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to get geoloc');
        $geoProvider->getAddressCollection('foo', 1);
    }

    public function testEmptyCollectionSuccess(): void
    {
        $nominatimGeocoder = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nominatimGeocoder->method('geocodeQuery')
            ->willReturn(new AddressCollection());

        $geoProvider = new GeoProvider($nominatimGeocoder, new NominatimToAddressAdapter());
        self::assertNull($geoProvider->getAddress('foo'));
    }

    public function testGetAddressSuccess(): void
    {
        $nominatimAddress = new NominatimAddress(
            providedBy: 'mock',
            adminLevels: new AdminLevelCollection(),
            coordinates: new Coordinates(50.63, 3.01),
            locality: 'Lille',
        );
        $nominatimAddress = $nominatimAddress
            ->withOSMId(6576374058)
            ->withOSMType('node');
        $addressCollection = new AddressCollection([$nominatimAddress]);
        $nominatimGeocoder = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $nominatimGeocoder->method('geocodeQuery')
            ->willReturn($addressCollection);

        $geoProvider = new GeoProvider($nominatimGeocoder, new NominatimToAddressAdapter());
        self::assertNotNull($geoProvider->getAddress('Lille'));
    }
}
