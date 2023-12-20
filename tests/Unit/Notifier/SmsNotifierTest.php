<?php

declare(strict_types=1);

namespace App\Tests\Unit\Notifier;

use App\Entity\User;
use App\Notifier\SmsNotifier;
use Monolog\Test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\TexterInterface;

final class SmsNotifierTest extends TestCase
{
    public function testNotify(): void
    {
        /** @var TexterInterface&MockObject $texterMock */
        $texterMock = $this->getMockBuilder(TexterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $texterMock->method('send')
            ->willThrowException(new TransportException('invalid number', new MockResponse('foobar')));

        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $notifier = new SmsNotifier($texterMock, $loggerMock);

        $user = new User();
        $user->setSmsNotifications(true)
            ->setPhoneNumber('+33610101010');
        $sentMessage = $notifier->notify($user, 'subject');
        self::assertNull($sentMessage);
    }
}
