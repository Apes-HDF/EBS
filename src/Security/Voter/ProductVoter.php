<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ProductVoter extends Voter
{
    // can be enums
    final public const EDIT = 'edit'; // test if a given user can edit a given product

    final public const DUPLICATE = 'duplicate'; // test if a given user can duplicate a given product
    final public const BORROW = 'borrow'; // test if a given user can borrow a given product

    final public const DELETE = 'delete'; // test if a given user can delete a given product

    final public const ATTRIBUTES = [
        self::EDIT,
        self::DUPLICATE,
        self::BORROW,
        self::DELETE,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Product || !\in_array($attribute, self::ATTRIBUTES, true)) {
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

        /** @var Product $subject */

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DUPLICATE => $this->canDuplicate($subject, $user),
            self::BORROW => $this->canBorrow($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canEdit(Product $product, User $user): bool
    {
        return $product->isOwner($user);
    }

    private function canDuplicate(Product $product, User $user): bool
    {
        return $product->isOwner($user);
    }

    private function canBorrow(Product $product, User $user): bool
    {
        // 1. we can't borrow or own products
        if ($user === $product->getOwner()) {
            return false;
        }

        return true;
    }

    private function canDelete(Product $product, User $user): bool
    {
        return $product->isOwner($user) && !$product->hasOngoingServiceRequests();
    }
}
