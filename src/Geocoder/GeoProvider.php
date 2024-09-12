<?php

declare(strict_types=1);

namespace App\Geocoder;

use App\Entity\Address;
use App\Geocoder\Adapter\NominatimToAddressAdapter;
use Geocoder\Exception\Exception;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Nominatim\Model\NominatimAddress;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;

class GeoProvider implements GeoProviderInterface
{
    public function __construct(
        private readonly Provider $nominatimGeocoder,
        private readonly NominatimToAddressAdapter $adapter,
    ) {
    }

    /**
     * Get a collection of address for a given location query.
     */
    public function getAddressCollection(string $text, int $limit, string $country = self::DEFAULT_COUNTRY): AddressCollection
    {
        $query = GeocodeQuery::create($text)
            ->withLimit($limit)
            ->withLocale($country)
        ;
        try {
            /** @var AddressCollection $collection */
            $collection = $this->nominatimGeocoder->geocodeQuery($query);
        } catch (Exception $e) {
            throw new \RuntimeException(\sprintf('Unable to get geoloc of %s: %s', $text, $e->getMessage()));
        }

        return $collection;
    }

    /**
     * Get the first result of a query as an Address entity instance.
     */
    public function getAddress(string $text): ?Address
    {
        $collection = $this->getAddressCollection($text, 1);

        // invalid address
        if ($collection->isEmpty()) {
            return null;
        }

        $address = new Address();
        /** @var NominatimAddress $geoAddress */
        $geoAddress = $collection->first();
        $this->adapter->fill($address, $geoAddress);

        return $address;
    }
}
