<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\TimestampableEntity;
use App\Enum\Group\GroupOfferType;
use App\Repository\GroupOfferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupOfferRepository::class)]
#[ORM\Table(name: 'group_offer')]
#[ORM\Index(columns: ['type'])]
class GroupOffer implements \Stringable
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
    private Uuid $id;

    /**
     * Related group.
     */
    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Group $group;

    /**
     * Short name of the offer.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    /**
     * Type of offer.
     */
    #[ORM\Column(name: 'type', type: 'string', nullable: false, enumType: GroupOfferType::class)]
    #[Assert\NotBlank]
    protected GroupOfferType $type;

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
     * If the offer is visible on the front site. Can be use to deactivate offers
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

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): GroupOffer
    {
        $this->group = $group;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): GroupOffer
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): GroupOfferType
    {
        return $this->type;
    }

    public function setType(GroupOfferType $type): GroupOffer
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

    public function setActive(bool $active): GroupOffer
    {
        $this->active = $active;

        return $this;
    }
}
