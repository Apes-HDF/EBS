<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter\ServiceRequest;

use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Security\Voter\ServiceRequest\ServiceRequestVoter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @see ServiceRequestVoter
 */
final class ServiceRequestVoterTest extends KernelTestCase
{
    private function createServiceRequest(): ServiceRequest
    {
        $owner = new User();
        $recipient = new User();

        return (new ServiceRequest())
            ->setOwner($owner)
            ->setRecipient($recipient);
    }

    public function testVoteRequestServiceVoterUserNotLoggedAccessDenied(): void
    {
        $voter = new ServiceRequestVoter();
        $token = new NullToken(); // act as if the user was not logged
        $subject = $this->createServiceRequest();
        $attribute = ServiceRequestVoter::VIEW;

        self::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $subject, [$attribute]));
    }
}
