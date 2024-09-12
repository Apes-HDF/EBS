<?php

declare(strict_types=1);

namespace App\Security\Voter\ServiceRequest;

use App\Entity\ServiceRequest;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ServiceRequestVoter extends Voter
{
    final public const VIEW = 'view'; // test if a given user can view a service request (including conversation)
    final public const ATTRIBUTES = [
        self::VIEW,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof ServiceRequest || !\in_array($attribute, self::ATTRIBUTES, true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // the user must be logged in; if not, deny access
        if (!$user instanceof User) {
            return false;
        }

        /** @var ServiceRequest $subject */

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    /**
     * A user can view the conversation of a service request if he is the owner
     * or the recipient of the service.
     */
    private function canView(ServiceRequest $serviceRequest, User $user): bool
    {
        return $serviceRequest->isOwnerOrRecipient($user);
    }
}
