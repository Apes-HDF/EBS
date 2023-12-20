<?php

declare(strict_types=1);

namespace App\Tests\Integration\Twig\Extension;

use App\Entity\User;
use App\Test\ContainerTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserExtensionTest extends KernelTestCase
{
    use ContainerTrait;
    use RefreshDatabaseTrait;

    /**
     * Better coverage.
     */
    public function testUserExtension(): void
    {
        self::bootKernel();
        $userExtension = $this->getUserExtension();
        $user = new User();
        $name = 'apes.png';
        $user->setAvatar($name);
        $publicUrl = $userExtension->getPublicUrl($user);
        self::assertSame('/storage/uploads/user/apes.png', $publicUrl);
    }
}
