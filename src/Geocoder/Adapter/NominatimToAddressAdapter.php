<?php

declare(strict_types=1);

namespace App\Geocoder\Adapter;

use App\Entity\Address;
use Geocoder\Provider\Nominatim\Model\NominatimAddress;

final class NominatimToAddressAdapter
{
    /**
     * Fill an address from a nominatim address object.
     */
    public function fill(Address $address, NominatimAddress $nominatimAddress): void
    {
        $address->setAddress($nominatimAddress->getStreetNumber().' '.$nominatimAddress->getStreetName());
        if ($nominatimAddress->getCoordinates() !== null) {
            $address->setLatitude((string) $nominatimAddress->getCoordinates()->getLatitude());
            $address->setLongitude((string) $nominatimAddress->getCoordinates()->getLongitude());
        }

        $address->setStreetName((string) $nominatimAddress->getStreetName());
        $address->setStreetNumber((string) $nominatimAddress->getStreetNumber());
        $address->setSubLocality((string) $nominatimAddress->getSubLocality());
        $address->setLocality((string) $nominatimAddress->getLocality());
        $address->setPostalCode((string) $nominatimAddress->getPostalCode());
        $address->setProvidedBy($nominatimAddress->getProvidedBy());
        $address->setAttribution((string) $nominatimAddress->getAttribution());
        $address->setDisplayName((string) $nominatimAddress->getDisplayName());
        $address->setOsmType((string) $nominatimAddress->getOSMType());

        if ($nominatimAddress->getOSMId() !== null) {
            $address->setOsmId($nominatimAddress->getOSMId());
        }
    }
}
