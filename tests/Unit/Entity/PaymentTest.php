<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Payment;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class PaymentTest extends TestCase
{
    public function testPayment(): void
    {
        $payment = new Payment();
        $id = Uuid::v6();
        self::assertSame((string) $id, $payment->setId((string) $id)->getId());
        $user = new User();
        self::assertSame($user, $payment->setUser($user)->getUser());
    }

    public function testPaymentIsPaid(): void
    {
        $payment = new Payment();
        self::assertFalse($payment->isPaid());
        self::assertNull($payment->getStatus());

        // offline
        $payment->setDetails([
            'paid' => true,
            'status' => 'captured',
        ]);
        self::assertTrue($payment->isPaid());
        self::assertSame('captured', $payment->getStatus());

        // test and prod mode
        $payment->setDetails([
            'paymment' => [],
        ]);
        self::assertFalse($payment->isPaid());
        self::assertNull($payment->getStatus());
        $payment->setDetails([
            'payment' => [
                'status' => 'paid',
            ],
        ]);
        self::assertTrue($payment->isPaid());
        self::assertSame('paid', $payment->getStatus());
    }
}
