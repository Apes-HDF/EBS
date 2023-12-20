<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Doctrine\Behavior\TimestampableEntity;
use App\Enum\Product\ProductType;
use App\Repository\CategoryRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table]
#[ORM\Index(columns: ['type'])]
#[Gedmo\Tree(type: 'nested')]
#[AppAssert\Constraints\Category\CategoryParentNotSelf]
class Category implements \Stringable, ImageInterface
{
    use TimestampableEntity;

    /**
     * Generates a V6 uuid.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    /**
     * Optional parent for the category.
     */
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[Gedmo\TreeParent]
    private ?self $parent = null;

    /**
     * @var Collection<int, self> $children
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: Types::INTEGER)]
    private int $lft;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: Types::INTEGER)]
    private int $lvl;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: Types::INTEGER)]
    private int $rgt;

    /**
     * Associated product type. Object or service.
     */
    #[ORM\Column(name: 'type', type: 'string', nullable: false, enumType: ProductType::class)]
    #[Assert\NotBlank]
    #[Gedmo\TreeRoot(identifierMethod: 'getType')]
    protected ProductType $type;

    /**
     * Short and main name of the category.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    /**
     * SEO friendly name for URLs.
     */
    #[ORM\Column(length: 255, unique: false)]
    #[Gedmo\Slug(fields: ['name'])]
    private string $slug;

    /**
     * Tells if the category is visible on the search form.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $enabled = true;

    /**
     * Default image for the objects associated to the category when not having
     * specific images.
     */
    #[ORM\Column(nullable: true)]
    private ?string $image = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function addChild(self $category): self
    {
        if (!$this->children->contains($category)) {
            $this->children->add($category);
            $category->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $category): self
    {
        if ($this->children->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getParent() === $this) {
                $category->setParent(null);
            }
        }

        return $this;
    }

    public function getType(): ProductType
    {
        return $this->type;
    }

    public function setType(ProductType $type): self
    {
        $this->type = $type;

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

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Create a dummy empty object with non nullable fields initialized.
     */
    public static function getForEmptyData(): Category
    {
        return (new self())
            ->setId(Uuid::v6())
            ->setType(ProductType::OBJECT)
            ->setName('')
            ->setSlug('');
    }

    /** End of basic 'etters ———————————————————————————————————————————————— */
    public function hasParent(): bool
    {
        return $this->parent !== null;
    }

    public function getNameWithIndent(): string
    {
        return $this->hasParent() ? str_repeat('—', $this->lvl).'> '.$this->getName() : $this->getName();
    }
}
