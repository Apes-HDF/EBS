<?php

declare(strict_types=1);

namespace App\MessageHandler\Command\User;

use App\Doctrine\Manager\UserManager;
use App\Entity\User;
use App\Geocoder\Adapter\NominatimToAddressAdapter;
use App\Message\Command\User\UpdateAddressCommand;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
/**
 * Command that create or modify the main address associated to a user.
 */
final class UpdateAddressCommandHandler
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
        private readonly NominatimToAddressAdapter $adapter,
    ) {
    }

    public function __invoke(UpdateAddressCommand $message): void
    {
        $user = $this->userRepository->find($message->id);
        Assert::isInstanceOf($user, User::class);

        // we keep address, addressSupplement, and country from user input
        $currentUserAddress = $user->getAddress();

        // new address, then take the DTO which is already good
        if ($currentUserAddress === null) {
            $currentUserAddress = $message->userAddress;
        } else {
            // otherwise override the properties of the old address
            $currentUserAddress->setFromAddressUpdateStep1($message->userAddress);
        }

        // we take other properties from the Geocoder address
        $this->adapter->fill($currentUserAddress, $message->newAddress);

        $user->setAddress($currentUserAddress); // we must set if it's a new address
        $this->userManager->save($user, true);
    }
}
