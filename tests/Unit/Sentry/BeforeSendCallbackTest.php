<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sentry;

use App\Sentry\BeforeSendCallback;
use PHPUnit\Framework\TestCase;
use Sentry\Event;
use Sentry\EventHint;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class BeforeSendCallbackTest extends TestCase
{
    /**
     * @dataProvider ignoredProvider
     */
    public function testClientErrorsAreNotSent(\Throwable $exception): void
    {
        self::assertNull($this->send($exception));
    }

    /**
     * @return iterable<string, array{\Throwable}>
     */
    public static function ignoredProvider(): iterable
    {
        yield '404' => [new NotFoundHttpException()];
        yield '403' => [new AccessDeniedHttpException()];
        yield 'access denied' => [new AccessDeniedException()];
        yield 'authentication' => [new AuthenticationException()];
    }

    public function testServerErrorsAreSent(): void
    {
        self::assertInstanceOf(Event::class, $this->send(new \RuntimeException('boom')));
        self::assertInstanceOf(Event::class, $this->send(new ServiceUnavailableHttpException()));
    }

    public function testEventWithoutExceptionIsSent(): void
    {
        self::assertInstanceOf(Event::class, (new BeforeSendCallback())(Event::createEvent(), null));
    }

    private function send(\Throwable $exception): ?Event
    {
        return (new BeforeSendCallback())(Event::createEvent(), EventHint::fromArray(['exception' => $exception]));
    }
}
