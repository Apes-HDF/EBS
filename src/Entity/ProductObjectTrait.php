<?php

declare(strict_types=1);

namespace App\Entity;

use App\Form\Type\Product\AbstractProductFormType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This trait contains the specific fields for the objects to lend.
 *
 * @see Product
 */
trait ProductObjectTrait
{
    /**
     * Age of the object (free text).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 255, groups: [AbstractProductFormType::class])]
    protected ?string $age = null;

    /**
     * Deposit for the object (caution 🇫🇷) in case the object is damaged by the
     * borrower).
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $deposit = null;

    /**
     * Deposit currency.
     *
     * @see https://en.wikipedia.org/wiki/ISO_4217
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    protected ?string $currency = self::DEFAULT_CURRENCY;

    /**
     * Preferred duration of the loan for a product.
     * eg: 2 two days.
     */
    #[ORM\Column(nullable: true)]
    private ?string $preferredLoanDuration = null;

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(?string $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getDeposit(): ?int
    {
        return $this->deposit;
    }

    public function setDeposit(?int $deposit): self
    {
        $this->deposit = $deposit;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getPreferredLoanDuration(): ?string
    {
        return $this->preferredLoanDuration;
    }

    public function setPreferredLoanDuration(?string $preferredLoanDuration): self
    {
        $this->preferredLoanDuration = $preferredLoanDuration;

        return $this;
    }

    public function getDepositReal(): ?int
    {
        if ($this->deposit === null) {
            return $this->deposit;
        }

        return $this->deposit / 100;
    }
}
