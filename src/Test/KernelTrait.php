<?php

declare(strict_types=1);

namespace App\Test;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Repository\UserRepository;
use App\Tests\TestReference;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait KernelTrait
{
    use ContainerTrait;

    public function login(KernelBrowser|Client $client, string $id): void
    {
        /** @var ContainerInterface $container */
        $container = $client->getContainer();
        $this->fixDoctrineBug($container);

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->get($id);
        $client->loginUser($user);
    }

    public function logout(KernelBrowser $client): void
    {
        $client->request('GET', '/logout');
        self::assertResponseRedirects();
        $client->followRedirect();
    }

    public function loginAsAdmin(KernelBrowser|Client $client): void
    {
        $this->login($client, TestReference::ADMIN_LOIC);
    }

    public function loginAsSarah(KernelBrowser|Client $client): void
    {
        $this->login($client, TestReference::ADMIN_SARAH);
    }

    public function loginAsKevin(KernelBrowser|Client $client): void
    {
        $this->login($client, TestReference::ADMIN_KEVIN);
    }

    public function loginAsUser(KernelBrowser|Client $client): void
    {
        $this->login($client, TestReference::USER_17);
    }

    /**
     * - No address associated yet.
     * - Is group admin of Group 1.
     */
    public function loginAsUser16(KernelBrowser|Client $client): void
    {
        $this->login($client, TestReference::USER_16);
    }

    /**
     * - Has a pending invitation for the group 1 group.
     */
    public function loginAsUser11(KernelBrowser|Client $client): void
    {
        $this->login($client, TestReference::USER_11);
    }

    /**
     * - Has a published product on the Apes place.
     */
    public function loginAsPlaceApes(KernelBrowser|Client $client): void
    {
        $this->login($client, TestReference::PLACE_APES);
    }
}
