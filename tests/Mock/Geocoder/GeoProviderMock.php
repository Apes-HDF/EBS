<?php

declare(strict_types=1);

namespace App\Tests\Mock\Geocoder;

use App\Entity\Address;
use App\Geocoder\GeoProviderInterface;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Provider\Nominatim\Model\NominatimAddress;

use function Symfony\Component\String\u;

final class GeoProviderMock implements GeoProviderInterface
{
    /**
     * Returns a dummy address collection.
     */
    public function getAddressCollection(string $text, int $limit, string $country = self::DEFAULT_COUNTRY): AddressCollection
    {
        // no results
        if (u($text)->lower()->trim()->containsAny('ez, 000, 000')) {
            return new AddressCollection();
        }

        // one dummy result
        $adminLevels = new AdminLevelCollection();
        $address = new NominatimAddress(
            providedBy: 'mock',
            adminLevels: $adminLevels,
            locality: $text,
        );

        return new AddressCollection([
            $address,
        ]);
    }

    /**
     * Mock geocoder calls to make to CI more reliable.
     */
    public function getAddress(string $text): ?Address
    {
        if (u($text)->lower()->trim()->containsAny('lille')) {
            return (new Address())
                ->setAddress($text)
                ->setLocality($text)
                ->setDisplayName($text)
                ->setLatitude('50.6365654')
                ->setLongitude('3.0635282');
        }

        throw new \LogicException("Mock for $text not implemented");
    }
}
