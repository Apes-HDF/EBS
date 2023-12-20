<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User\Account;

use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see ProfileAction
 */
final class ProfileActionTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use KernelTrait;
    use ContainerRepositoryTrait;

    private const ROUTE = '/fr/utilisateur';

    public function testMemberNotFoundError(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::ROUTE.'/'.TestReference::UUID_404);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testMemberWithoutAddress(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::ROUTE.'/'.TestReference::USER_11);
        self::assertResponseIsSuccessful();
    }

    public function testMember(): void
    {
        $client = self::createClient();
        $this->loginAsUser16($client);
        $client->request('GET', self::ROUTE.'/'.TestReference::ADMIN_SARAH);
        self::assertResponseIsSuccessful();
    }
}
