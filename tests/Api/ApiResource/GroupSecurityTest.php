<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace App\Tests\Api\ApiResource;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see Group
 * @see GroupsProvider
 */
final class GroupSecurityTest extends ApiTestCase
{
    use ReloadDatabaseTrait;
    use KernelTrait;

    private const DISABLE_CHILD_SERVICES_URL = '/api/groups/'.TestReference::GROUP_1.'/disable_child_services';

    public function testUserGroupsAnonymousAccessIsDenied(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/user_groups');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $client->request('GET', '/api/user_groups/'.TestReference::USER_GROUP_LOIC_GROUP_7);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUserGroupsWriteOperationsAreRemoved(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin($client);
        $client->request('POST', '/api/user_groups', ['json' => []]);
        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
        $client->request('PATCH', '/api/user_groups/'.TestReference::USER_GROUP_LOIC_GROUP_7, ['json' => [], 'headers' => ['Content-Type' => 'application/merge-patch+json']]);
        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
        $client->request('DELETE', '/api/user_groups/'.TestReference::USER_GROUP_LOIC_GROUP_7);
        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testUserGroupsAccessRules(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('GET', '/api/user_groups');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        // membership of Loïc at group 7, user 17 is neither concerned nor group admin
        $client->request('GET', '/api/user_groups/'.TestReference::USER_GROUP_LOIC_GROUP_7);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->loginAsAdmin($client);
        $client->request('GET', '/api/user_groups');
        self::assertResponseIsSuccessful();
        $response = $client->request('GET', '/api/user_groups/'.TestReference::USER_GROUP_LOIC_GROUP_7);
        self::assertResponseIsSuccessful();
        $userGroup = $response->toArray();
        // null dates are skipped by API Platform
        self::assertEmpty(array_diff(
            array_keys($userGroup),
            ['@context', '@id', '@type', 'id', 'userId', 'group', 'membership', 'mainAdminAccount', 'startAt', 'endAt', 'payedAt'],
        ));
        self::assertArrayNotHasKey('user', $userGroup);
        self::assertSame(TestReference::ADMIN_LOIC, $userGroup['userId']);
        self::assertSame('/api/groups/'.TestReference::GROUP_7, $userGroup['group']);
    }

    public function testAnonymousAccessIsDenied(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/groups?services_enabled=true');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $client->request('GET', '/api/groups/'.TestReference::GROUP_1);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $client->request('PATCH', self::DISABLE_CHILD_SERVICES_URL, ['headers' => ['Content-Type' => 'application/merge-patch+json']]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetExposesOnlyPublicFields(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $response = $client->request('GET', '/api/groups/'.TestReference::GROUP_1);
        self::assertResponseIsSuccessful();
        self::assertEqualsCanonicalizing(
            ['@context', '@id', '@type', 'id', 'name', 'servicesEnabled', 'parentsRecursively', 'childrenRecursively'],
            array_keys($response->toArray())
        );
    }

    public function testListGroupsOfAnotherUserIsForbidden(): void
    {
        $client = self::createClient();
        $this->loginAsUser($client);
        $client->request('GET', '/api/groups?services_enabled=true&user='.TestReference::USER_16);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $client->request('GET', '/api/groups?services_enabled=true&user='.TestReference::USER_17);
        self::assertResponseIsSuccessful();
    }

    public function testDisableChildServicesRequiresGroupAdmin(): void
    {
        $client = self::createClient();
        $options = ['headers' => ['Content-Type' => 'application/merge-patch+json']];

        $this->loginAsUser($client);
        $client->request('PATCH', self::DISABLE_CHILD_SERVICES_URL, $options);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $this->loginAsUser16($client);
        $client->request('PATCH', self::DISABLE_CHILD_SERVICES_URL, $options);
        self::assertResponseIsSuccessful();
    }
}
