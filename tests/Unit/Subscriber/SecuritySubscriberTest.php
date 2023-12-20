<?php

declare(strict_types=1);

namespace App\Tests\Unit\Subscriber;

use App\Subscriber\SecuritySubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class SecuritySubscriberTest extends TestCase
{
    public function testSecuritySubscriber(): void
    {
        self::assertSame([LoginSuccessEvent::class => 'onLoginSuccess'], SecuritySubscriber::getSubscribedEvents());
    }
}
