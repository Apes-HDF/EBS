<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto\Security;

use App\Entity\User;
use App\Message\Command\Security\AccountCreateStep1Command;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

final class AccountCreateStep1CommandTest extends TestCase
{
    public function testConstructSuccess(): void
    {
        $user = new User();
        $user->setEmail('foo@example.com');
        $command = new AccountCreateStep1Command($user);
        self::assertSame('foo@example.com', $command->email);
    }

    /**
     * @return iterable<array{0: ?string}>
     */
    public function provideConstructException(): iterable
    {
        yield [''];
        yield ['toto'];
    }

    /**
     * @dataProvider provideConstructException
     */
    public function testConstructInvalidEmailException(string $email): void
    {
        $user = new User();
        $user->setEmail($email);
        $this->expectException(InvalidArgumentException::class);
        new AccountCreateStep1Command($user);
    }
}
