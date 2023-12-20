<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\Menu\LinkType;
use App\Enum\SocialMediaType;
use App\Repository\MenuItemRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
#[ORM\Table]
#[AppAssert\Constraints\MenuItem\MenuItemParentNotSelf]
class MenuItem implements \Stringable
{
    /**
     * Ordering starts at 0 not 1.
     */
    final public const POSITION_FIRST = 0;

    /** Generate a V6 uuid */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    /**
     * Name of menu item.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\When(
        expression: 'this.isLink()',
        constraints: [
            new Assert\NotBlank(message: 'menu.validator.field'),
        ],
    )]
    private ?string $name = null;

    /**
     * Define if the item is a link of a social network or not.
     */
    #[ORM\Column(name: 'linkType', type: 'string', enumType: LinkType::class)]
    private LinkType $linkType = LinkType::LINK;

    /**
     * Type of social media.
     */
    #[ORM\Column(name: 'mediaType', type: 'string', nullable: true, enumType: SocialMediaType::class)]
    #[Assert\When(
        expression: 'this.isSocialNetwork()',
        constraints: [
            new Assert\NotBlank(message: 'menu.validator.field'),
        ],
    )]
    private ?SocialMediaType $mediaType = null;

    /**
     * Link of the menu item.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $link;

    /**
     * Optional parent for menu item.
     */
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    /**
     * @var Collection<int, self> $children
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $children;

    /**
     * Menu related to items.
     */
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'items')]
    private Menu $menu;

    /**
     * Position of the item in the front menu.
     */
    #[Gedmo\SortablePosition]
    #[ORM\Column(name: 'position', type: 'integer')]
    private int $position;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? $this->link;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLinkType(): LinkType
    {
        return $this->linkType;
    }

    public function setLinkType(LinkType $linkType): void
    {
        $this->linkType = $linkType;
    }

    public function getMediaType(): ?SocialMediaType
    {
        return $this->mediaType;
    }

    public function setMediaType(?SocialMediaType $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }

    /**
     * @param Collection<int, self> $children
     */
    public function setChildren(Collection $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function getMenu(): Menu
    {
        return $this->menu;
    }

    public function setMenu(Menu $menu): MenuItem
    {
        $this->menu = $menu;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPositionHuman(): int
    {
        return $this->position + 1;
    }

    public function isFirst(): bool
    {
        return $this->position === self::POSITION_FIRST;
    }

    public function up(): int
    {
        return $this->position - 1;
    }

    public function down(): int
    {
        return $this->position + 1;
    }

    public function isLink(): bool
    {
        return $this->linkType === LinkType::LINK;
    }

    public function isSocialNetwork(): bool
    {
        return $this->linkType === LinkType::SOCIAL_NETWORK;
    }
}
