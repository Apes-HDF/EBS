<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Manager\UserManager;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Properties for the "change login" feature: the new email is only applied once
 * the user has confirmed it with the token sent to this new address.
 *
 * @see UserManager
 */
trait UserPendingEmailTrait
{
    /**
     * New email requested by the user, not confirmed yet (normalized).
     */
    #[ORM\Column(length: 180, nullable: true)]
    protected ?string $pendingEmail = null;

    /**
     * Token to confirm the new email.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $pendingEmailToken = null;

    /**
     * Date until the pending email token is valid (24 hours).
     *
     * @see UserManager::PENDING_EMAIL_TOKEN_EXPIRATION_TIME
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeInterface $pendingEmailExpiresAt = null;

    public function getPendingEmail(): ?string
    {
        return $this->pendingEmail;
    }

    public function setPendingEmail(?string $pendingEmail): self
    {
        $this->pendingEmail = $pendingEmail;

        return $this;
    }

    public function getPendingEmailToken(): ?string
    {
        return $this->pendingEmailToken;
    }

    public function setPendingEmailToken(?string $pendingEmailToken): self
    {
        $this->pendingEmailToken = $pendingEmailToken;

        return $this;
    }

    public function getPendingEmailExpiresAt(): ?\DateTimeInterface
    {
        return $this->pendingEmailExpiresAt;
    }

    public function setPendingEmailExpiresAt(?\DateTimeInterface $pendingEmailExpiresAt): self
    {
        $this->pendingEmailExpiresAt = $pendingEmailExpiresAt;

        return $this;
    }

    /**
     * Test if the token is still valid.
     */
    public function isPendingEmailTokenExpired(\DateTimeInterface $now): bool
    {
        return $now > $this->getPendingEmailExpiresAt();
    }

    /**
     * Reset all properties after a successful confirmation.
     */
    public function resetPendingEmail(): void
    {
        $this->setPendingEmail(null);
        $this->setPendingEmailToken(null);
        $this->setPendingEmailExpiresAt(null);
    }
}
