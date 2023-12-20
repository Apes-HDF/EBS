<?php

declare(strict_types=1);

namespace App\MessageHandler\Query\User;

use App\Geocoder\GeoProviderInterface;
use App\Message\Query\Admin\User\UserAddressQuery;
use Geocoder\Model\AddressCollection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UserAddressQueryHandler
{
    private const WITH_LIMIT = 3;

    public function __construct(
        private readonly GeoProviderInterface $geoProvider
    ) {
    }

    /**
     * We consider the message valid at this point.
     */
    public function __invoke(UserAddressQuery $message): AddressCollection
    {
        return $this->geoProvider->getAddressCollection(
            $message->getAddressForQuery(),
            self::WITH_LIMIT,
        );
    }
}
