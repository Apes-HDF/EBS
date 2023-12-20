<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Manager\UserManager;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Properties for the user email confirmation feature.
 *
 * @see UserManager
 */
trait UserConfirmationTrait
{
    /**
     * Token to confirm the validity of the user's email.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $confirmationToken = null;

    /**
     * Date until the confirmation token is valid (24 hours).
     *
     * @see UserManager::CONFIRMATION_TOKEN_EXPIRATION_TIME
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeInterface $confirmationExpiresAt = null;

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getConfirmationExpiresAt(): ?\DateTimeInterface
    {
        return $this->confirmationExpiresAt;
    }

    public function setConfirmationExpiresAt(?\DateTimeInterface $confirmationExpiresAt): self
    {
        $this->confirmationExpiresAt = $confirmationExpiresAt;

        return $this;
    }

    /**
     * Test if the token is still valid.
     */
    public function isConfirmationTokenExpired(\DateTimeInterface $now): bool
    {
        return $now > $this->getConfirmationExpiresAt();
    }

    /**
     * Reset all properties after a successful confirmation.
     */
    public function resetConfirmation(): void
    {
        $this->setConfirmationToken(null);
        $this->setConfirmationExpiresAt(null);
    }

    /**
     * Mark the email as confirmed, user can now login.
     */
    public function confirmEmail(): void
    {
        $this->setEmailConfirmed(true);
    }
}
