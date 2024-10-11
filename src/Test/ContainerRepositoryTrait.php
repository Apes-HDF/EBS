<?php

declare(strict_types=1);

namespace App\Test;

use App\Doctrine\Manager\ProductManager;
use App\Doctrine\Manager\UserManager;
use App\Repository\AddressRepository;
use App\Repository\CategoryRepository;
use App\Repository\ConfigurationRepository;
use App\Repository\GroupOfferRepository;
use App\Repository\GroupRepository;
use App\Repository\MenuItemRepository;
use App\Repository\MenuRepository;
use App\Repository\MessageRepository;
use App\Repository\PaymentRepository;
use App\Repository\PlatformOfferRepository;
use App\Repository\ProductAvailabilityRepository;
use App\Repository\ProductRepository;
use App\Repository\ServiceRequestRepository;
use App\Repository\UserGroupRepository;
use App\Repository\UserRepository;

/**
 * Helper with goof type hints.
 */
trait ContainerRepositoryTrait
{
    /**
     * @see https://stackoverflow.com/q/69937246/633864
     */
    public function fixDoctrine(): void
    {
        self::getContainer()->get('doctrine')->getManager()->clear();
    }

    public function getAddressRepository(): AddressRepository
    {
        return self::getContainer()->get(AddressRepository::class);
    }

    public function getCategoryRepository(): CategoryRepository
    {
        return self::getContainer()->get(CategoryRepository::class);
    }

    public function getGroupRepository(): GroupRepository
    {
        return self::getContainer()->get(GroupRepository::class);
    }

    public function getUserRepository(): UserRepository
    {
        return self::getContainer()->get(UserRepository::class);
    }

    public function getUserGroupRepository(): UserGroupRepository
    {
        return self::getContainer()->get(UserGroupRepository::class);
    }

    public function getGroupOfferRepository(): GroupOfferRepository
    {
        return self::getContainer()->get(GroupOfferRepository::class);
    }

    public function getUserManager(): UserManager
    {
        return self::getContainer()->get(UserManager::class);
    }

    public function getProductManager(): ProductManager
    {
        return self::getContainer()->get(ProductManager::class);
    }

    public function getConfigurationRepository(): ConfigurationRepository
    {
        return self::getContainer()->get(ConfigurationRepository::class);
    }

    public function getProductRepository(): ProductRepository
    {
        return self::getContainer()->get(ProductRepository::class);
    }

    public function getPaymentRepository(): PaymentRepository
    {
        return self::getContainer()->get(PaymentRepository::class);
    }

    public function getProductAvailabilityRepository(): ProductAvailabilityRepository
    {
        return self::getContainer()->get(ProductAvailabilityRepository::class);
    }

    public function getMenuRepository(): MenuRepository
    {
        return self::getContainer()->get(MenuRepository::class);
    }

    public function getMenuItemRepository(): MenuItemRepository
    {
        return self::getContainer()->get(MenuItemRepository::class);
    }

    public function getMessageRepository(): MessageRepository
    {
        return self::getContainer()->get(MessageRepository::class);
    }

    public function getServiceRequestRepository(): ServiceRequestRepository
    {
        return self::getContainer()->get(ServiceRequestRepository::class);
    }

    public function getPlatformOfferRepository(): PlatformOfferRepository
    {
        return self::getContainer()->get(PlatformOfferRepository::class);
    }
}
