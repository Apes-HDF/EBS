<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler\Security;

use App\Entity\User;
use App\Enum\User\UserType;
use App\Message\Command\Security\AccountCreateStep2Command;
use App\MessageHandler\Command\Security\AccountCreateStep2CommandHandler;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

final class AccountCreateStep2CommandHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function testInvokeException(): void
    {
        self::bootKernel();
        $handler = self::getContainer()->get(AccountCreateStep2CommandHandler::class);
        self::assertInstanceOf(AccountCreateStep2CommandHandler::class, $handler);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('This hanlder can only create users or places');
        $user = (new User())
            ->setId(Uuid::fromString(TestReference::USER_17))
            ->setType(UserType::ADMIN)
            ->setPlainPassword('foo')
        ;
        $message = new AccountCreateStep2Command($user);
        $handler($message);
    }
}
