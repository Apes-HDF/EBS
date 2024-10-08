<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler\Payment;

use App\Entity\Payment;
use App\Entity\PaymentToken;
use App\Entity\User;
use App\Message\Command\Payment\PlatformMembershipPaidCommand;
use App\MessageHandler\Command\Payment\PlatformMembershipPaidCommandHandler;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PlatformMembershipPaidCommandHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use ContainerRepositoryTrait;

    /**
     * Just to test when the status is not "captured".
     */
    public function testDoneStatusFailed(): void
    {
        self::bootKernel();
        $handler = self::getContainer()->get(PlatformMembershipPaidCommandHandler::class);
        self::assertInstanceOf(PlatformMembershipPaidCommandHandler::class, $handler);

        $platformOffer = $this->getPlatformOfferRepository()->get(TestReference::PLATFORM_OFFER_1);
        $user = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);

        $message = new PlatformMembershipPaidCommand($platformOffer->getId(), $user->getId(), $this->getToken($user));
        $status = $handler($message);
        self::assertTrue($status->isNew());
    }

    private function getToken(User $user): PaymentToken
    {
        $token = new PaymentToken();
        $token->setGatewayName('offline');
        $payment = new Payment();
        $payment->setUser($user);
        $token->setDetails($payment);

        return $token;
    }

    /**
     * No error 500 if the user is already a member.
     */
    public function testDoneAlreadyMember(): void
    {
        self::bootKernel();
        $handler = self::getContainer()->get(PlatformMembershipPaidCommandHandler::class);
        self::assertInstanceOf(PlatformMembershipPaidCommandHandler::class, $handler);

        $platformOffer = $this->getPlatformOfferRepository()->get(TestReference::PLATFORM_OFFER_1);
        $user = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);
        $user->setMembershipPaid(false); // juste for the test
        $this->getUserManager()->save($user, true);
        $payment = $this->getPaymentRepository()->get(TestReference::PAYMENT_USER_16_1);
        $token = new PaymentToken();
        $token->setGatewayName('offline');
        $payment->setUser($user);
        $token->setDetails($payment);

        $message = new PlatformMembershipPaidCommand($platformOffer->getId(), $user->getId(), $token);
        $status = $handler($message);
        self::assertTrue($status->isCaptured());
        self::assertEmailCount(1);
        self::assertTrue($user->isMembershipPaid());
    }
}
