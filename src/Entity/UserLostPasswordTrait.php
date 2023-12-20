<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Manager\UserManager;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Properties for the "last password" feature.
 *
 * @see UserManager
 */
trait UserLostPasswordTrait
{
    /**
     * Token to confirm the modificiation of the password.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $lostPasswordToken = null;

    /**
     * Date until the password token is valid (1 hour).
     *
     * @see UserManager::LOST_PASSWORD_TOKEN_EXPIRATION_TIME
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeInterface $lostPasswordExpiresAt = null;

    public function getLostPasswordToken(): ?string
    {
        return $this->lostPasswordToken;
    }

    public function setLostPasswordToken(?string $lostPasswordToken): self
    {
        $this->lostPasswordToken = $lostPasswordToken;

        return $this;
    }

    public function getLostPasswordExpiresAt(): ?\DateTimeInterface
    {
        return $this->lostPasswordExpiresAt;
    }

    public function setLostPasswordExpiresAt(?\DateTimeInterface $lostPasswordExpiresAt): self
    {
        $this->lostPasswordExpiresAt = $lostPasswordExpiresAt;

        return $this;
    }

    /**
     * Reset all properties after a successful reset.
     */
    public function resetLostPawword(): void
    {
        $this->setLostPasswordToken(null);
        $this->setLostPasswordExpiresAt(null);
    }

    /**
     * Test if the token is still valid.
     */
    public function isLostPasswordTokenExpired(\DateTimeInterface $now): bool
    {
        return $now > $this->getLostPasswordExpiresAt();
    }
}
