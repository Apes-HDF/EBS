<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\Group;
use App\Entity\User;
use App\Test\ContainerRepositoryTrait;
use App\Test\KernelTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Text entered by users or group admins must be escaped on public pages.
 */
final class StoredTextEscapingTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use KernelTrait;
    use ContainerRepositoryTrait;

    private const PAYLOAD = '<script>alert(1)</script><img src=x onerror=alert(2)>';

    private function assertEscaped(KernelBrowser $client): void
    {
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('<script>alert(1)</script>', $content);
        self::assertStringNotContainsString('<img src=x', $content);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $content);
    }

    public function testUserDescriptionIsEscapedOnPublicProfile(): void
    {
        $client = self::createClient();
        /** @var User $user */
        $user = $this->getUserRepository()->find(TestReference::USER_17);
        $user->setDescription(self::PAYLOAD."\nsecond line");
        $this->getUserRepository()->save($user, true);

        $client->request('GET', '/fr/utilisateur/'.TestReference::USER_17);
        $this->assertEscaped($client);
        // line breaks of the description are still rendered
        self::assertMatchesRegularExpression('#onerror=alert\(2\)&gt;<br />\s*second line#', (string) $client->getResponse()->getContent());
    }

    public function testGroupDescriptionIsEscapedOnGroupPage(): void
    {
        $client = self::createClient();
        /** @var Group $group */
        $group = $this->getGroupRepository()->find(TestReference::GROUP_1);
        $group->setDescription(self::PAYLOAD);
        $this->getGroupRepository()->save($group, true);

        $client->request('GET', \sprintf('/fr/groupes/%s/%s', $group->getSlug(), TestReference::GROUP_1));
        $this->assertEscaped($client);
    }
}
