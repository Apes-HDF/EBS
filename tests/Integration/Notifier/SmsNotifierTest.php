<?php

declare(strict_types=1);

namespace App\Tests\Integration\Notifier;

use App\Entity\User;
use App\Notifier\SmsNotifier;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SmsNotifierTest extends KernelTestCase
{
    public function testNotify(): void
    {
        self::bootKernel();
        /** @var SmsNotifier $notifier */
        $notifier = self::getContainer()->get(SmsNotifier::class);
        $user = new User();
        $sentMessage = $notifier->notify($user, 'subject');
        self::assertNull($sentMessage);

        $user->setSmsNotifications(true)
            ->setPhoneNumber('0610101010');

        $sentMessage = $notifier->notify($user, 'subject');
        self::assertNull($sentMessage);
    }
}
