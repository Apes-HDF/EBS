<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Product;
use App\Entity\User;
use App\Security\Voter\ProductVoter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @see ProductVoter
 */
final class ProductVoterTest extends KernelTestCase
{
    private function createProduct(): Product
    {
        $user = new User();

        return (new Product())
            ->setOwner($user);
    }

    public function testVoteRequestServiceVoterUserNotLoggedAccessDenied(): void
    {
        $voter = new ProductVoter();
        $subject = $this->createProduct();
        $user = $subject->getOwner();
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [ProductVoter::BORROW]));
        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [ProductVoter::EDIT]));
        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [ProductVoter::DUPLICATE]));

        $otherUser = new User();
        $token = new UsernamePasswordToken($otherUser, 'main', $otherUser->getRoles());
        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $subject, [ProductVoter::BORROW]));
        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [ProductVoter::EDIT]));
        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [ProductVoter::DUPLICATE]));
    }
}
