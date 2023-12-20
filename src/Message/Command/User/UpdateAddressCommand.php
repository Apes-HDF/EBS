<?php

declare(strict_types=1);

namespace App\Message\Command\User;

use App\Entity\Address;
use App\MessageHandler\Command\User\UpdateAddressCommandHandler;
use Geocoder\Provider\Nominatim\Model\NominatimAddress;
use Symfony\Component\Uid\Uuid;

/**
 * @see AccountCreateFormType
 * @see UpdateAddressCommandHandler
 */
final class UpdateAddressCommand
{
    public function __construct(
        // the user id
        public readonly Uuid $id,

        // the user address input
        public Address $userAddress,

        // the selected address
        public NominatimAddress $newAddress,
    ) {
    }
}
