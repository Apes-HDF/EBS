<?php

declare(strict_types=1);

namespace App\Tests\Integration\Security\Checker;

use App\Security\Checker\AuthorizationChecker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class AuthorizationCheckerTest extends KernelTestCase
{
    public function testCheckGroupAdminRoleException(): void
    {
        self::bootKernel();
        /** @var AuthorizationChecker $service */
        $service = self::getContainer()->get(AuthorizationChecker::class);
        $this->expectException(AccessDeniedHttpException::class);
        $service->isGroupAdmin();
    }
}
