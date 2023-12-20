<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;
use Payum\Offline\Constants;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * @see https://github.com/Payum/Payum/blob/master/docs/symfony/get-it-started.md#configure
 */
#[ORM\Entity]
#[ORM\Table]
class Payment extends BasePayment
{
    use TimestampableEntity;

    /**
     * Generates a V6 uuid.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private string $id; // don't use a Uuid object here or the serialization will fail.

    /**
     * Related user (with good type hint), the client id is also stored in the
     * client_id colum as a simple string.
     */
    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(name: '`user`', nullable: false)]
    private User $user;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Payment
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Payment
    {
        $this->user = $user;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->details['method'] ?? 'NA';
    }

    public function isPaid(): bool
    {
        // offline payment
        if (\array_key_exists(Constants::FIELD_PAID, $this->details)) {
            return $this->details[Constants::FIELD_PAID] ?? false;
        }

        // test and prod mode

        return $this->getStatus() === Constants::FIELD_PAID;
    }

    public function getStatus(): ?string
    {
        // offline
        if (\array_key_exists(Constants::FIELD_STATUS, $this->details)) {
            return $this->details[Constants::FIELD_STATUS] ?? null;
        }

        // test and prod mode

        return $this->details['payment'][Constants::FIELD_STATUS] ?? null;
    }
}
