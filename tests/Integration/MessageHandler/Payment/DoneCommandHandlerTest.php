<?php

declare(strict_types=1);

namespace App\Tests\Integration\MessageHandler\Payment;

use App\Entity\Payment;
use App\Entity\PaymentToken;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Message\Command\Payment\DoneCommand;
use App\MessageHandler\Command\Payment\DoneCommandHandler;
use App\Test\ContainerRepositoryTrait;
use App\Tests\TestReference;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoneCommandHandlerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use ContainerRepositoryTrait;

    /**
     * Just to test when the status is not "captured".
     */
    public function testDoneStatusFailed(): void
    {
        self::bootKernel();
        $handler = self::getContainer()->get(DoneCommandHandler::class);
        self::assertInstanceOf(DoneCommandHandler::class, $handler);

        $groupOffer = $this->getGroupOfferRepository()->get(TestReference::GROUP_OFFER_GROUP_1_1);
        $user = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);

        $message = new DoneCommand($groupOffer->getId(), $user->getId(), $this->getToken($user));
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
        $handler = self::getContainer()->get(DoneCommandHandler::class);
        self::assertInstanceOf(DoneCommandHandler::class, $handler);

        $groupOffer = $this->getGroupOfferRepository()->get(TestReference::GROUP_OFFER_GROUP_1_1);
        $user = $this->getUserRepository()->get(TestReference::ADMIN_LOIC);
        $payment = $this->getPaymentRepository()->get(TestReference::PAYMENT_USER_16_1);
        $token = new PaymentToken();
        $token->setGatewayName('offline');
        $payment->setUser($user);
        $token->setDetails($payment);

        // meanwhile add the membership to test this specific case
        $userGroup = (new UserGroup())
            ->setUser($user)
            ->setGroup($groupOffer->getGroup());
        $user->addUserGroup($userGroup);
        $this->getUserRepository()->save($user);

        $message = new DoneCommand($groupOffer->getId(), $user->getId(), $token);
        $status = $handler($message);
        self::assertTrue($status->isCaptured());
    }
}
