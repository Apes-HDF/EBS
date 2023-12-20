<?php

declare(strict_types=1);

namespace App\Dto\User;

use App\Entity\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Nominatim\Model\NominatimAddress;

/**
 * These are the data we save after the step1 of the user address update.
 *
 * @see AddressController
 */
final class UserAddressStep1Data
{
    public function __construct(
        /**
         * Address entered by the user.
         *
         * @see AddressStep1FormType
         */
        public readonly Address $address,

        /**
         * Adresses matching the user address found by the Geocoder.
         */
        public readonly AddressCollection $addresses,
    ) {
    }

    /**
     * Get data for the view.
     *
     * @return array{address: Address, addresses: AddressCollection}
     */
    public function getData(): array
    {
        return [
            'address' => $this->address,
            'addresses' => $this->addresses,
        ];
    }

    /**
     * @return array<NominatimAddress>
     *
     * @throws \Exception
     */
    public function getAddressesAsArray(): array
    {
        /** @var array<NominatimAddress> $array */
        $array = iterator_to_array($this->addresses->getIterator());

        return $array;
    }
}
