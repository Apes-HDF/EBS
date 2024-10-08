<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Repository\ConfigurationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MembershipPaidVoter extends Voter
{
    public function __construct(
        private readonly ConfigurationRepository $configurationRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return User::MEMBERSHIP_PAID === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return !$this->configurationRepository->getInstanceConfigurationOrCreate()->getPaidMembership() || $user->isMembershipPaid();
    }
}
