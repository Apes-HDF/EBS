<?php

declare(strict_types=1);

namespace App\Doctrine\Behavior;

use App\Enum\OfferType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
abstract class AbstractOfferEntity implements \Stringable
{
    use TimestampableEntity;

    final public const DEFAULT_CURRENCY = 'EUR';

    /**
     * Generates a V6 uuid.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected Uuid $id;

    /**
     * Short name of the offer.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected string $name;

    /**
     * Type of offer.
     */
    #[ORM\Column(name: 'type', type: 'string', nullable: false, enumType: OfferType::class)]
    #[Assert\NotBlank]
    protected OfferType $type;

    /**
     * Price, we stored the amount multiplied by 100 so we can use an integer for
     * this property.
     */
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    protected int $price;

    /**
     * Associated currency for the price property.
     *
     * @see https://en.wikipedia.org/wiki/ISO_4217
     */
    #[ORM\Column(type: Types::STRING, nullable: false)]
    protected string $currency = self::DEFAULT_CURRENCY;

    /**
     * If the offer is visible on the front site. Can be used to deactivate offers
     * for some time.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $active = true;

    public function __toString()
    {
        return $this->name.' ('.$this->type->value.')';
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $uuid): self
    {
        $this->id = $uuid;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): OfferType
    {
        return $this->type;
    }

    public function setType(OfferType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getActualPrice(): int
    {
        return $this->price / 100;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
