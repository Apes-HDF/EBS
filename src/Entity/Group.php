<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Doctrine\Behavior\TimestampableEntity;
use App\Enum\Group\GroupMembership;
use App\Enum\Group\GroupType;
use App\Repository\GroupRepository;
use App\State\GroupsProvider;
use App\State\Processor\GroupChildServicesEnabledProcessor;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')] // we need escaping here, as group is a reserved word
#[ORM\Index(columns: ['type'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[AppAssert\Constraints\Group\GroupParentNotSelf]
#[ApiResource(
    operations: [
        new GetCollection(provider: GroupsProvider::class),
        new Patch(
            uriTemplate: '/groups/{id}/disable_child_services',
            input: false,
            processor: GroupChildServicesEnabledProcessor::class
        ),
    ]
)]
class Group implements \Stringable
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
     * Optional parent for the group.
     */
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    /**
     * Type of the group. Public group are accessible by anyone while a private group
     * requires an invitation.
     */
    #[ORM\Column(name: 'type', type: 'string', nullable: false, enumType: GroupType::class)]
    #[Assert\NotBlank]
    protected GroupType $type = GroupType::PUBLIC;

    /**
     * Short and main name of the group.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    /**
     * SEO friendly group name for URLs.
     */
    #[ORM\Column(length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    private string $slug;

    /**
     * Longer description of the group.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $description = null;

    /**
     * Optional external URL for the group.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $url = null;

    /**
     * @var Collection<int, self> $children
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    /**
     * membership of the group. It can be free or paying.
     */
    #[ORM\Column(name: 'membership', type: 'string', nullable: false, enumType: GroupMembership::class)]
    protected GroupMembership $membership = GroupMembership::FREE;

    /**
     *  If true, administrators can send invitations.
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $invitationByAdmin = false;

    /**
     * @var Collection<int, UserGroup>
     */
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: UserGroup::class, orphanRemoval: true)]
    private Collection $userGroups;

    /**
     * @var Collection<int, GroupOffer>
     */
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: GroupOffer::class, orphanRemoval: true)]
    #[ORM\OrderBy(['price' => 'ASC'])]
    private Collection $offers;

    /**
     * List of visible product in the group.
     *
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'groups')]
    private Collection $products;

    #[ORM\Column(type: 'boolean')]
    private bool $servicesEnabled = false;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->products = new ArrayCollection();
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

    public function getType(): GroupType
    {
        return $this->type;
    }

    public function setType(GroupType $type): Group
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $group): self
    {
        if (!$this->children->contains($group)) {
            $this->children->add($group);
            $group->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $group): self
    {
        // set the owning side to null (unless already changed)
        if ($this->children->removeElement($group) && $group->getParent() === $this) {
            $group->setParent(null);
        }

        return $this;
    }

    public function getMembership(): GroupMembership
    {
        return $this->membership;
    }

    public function setMembership(GroupMembership $membership): void
    {
        $this->membership = $membership;
    }

    public function isInvitationByAdmin(): bool
    {
        return $this->invitationByAdmin;
    }

    public function setInvitationByAdmin(bool $invitationByAdmin): self
    {
        $this->invitationByAdmin = $invitationByAdmin;

        return $this;
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): self
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
            $userGroup->setGroup($this);
        }

        return $this;
    }

    public function removeUserGroup(UserGroup $userGroup): self
    {
        $this->userGroups->removeElement($userGroup);

        return $this;
    }

    /**
     * @return Collection<int, GroupOffer>
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    /**
     * @param Collection<int, GroupOffer> $offers
     */
    public function setOffers(Collection $offers): Group
    {
        $this->offers = $offers;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addGroup($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            $product->removeGroup($this);
        }

        return $this;
    }

    public function getServicesEnabled(): bool
    {
        return $this->servicesEnabled;
    }

    public function setServicesEnabled(bool $servicesEnabled): void
    {
        $this->servicesEnabled = $servicesEnabled;
    }

    // End of basic 'etters ----------------------------------------------------

    /**
     * @return array<string, string>
     */
    public function getRoutingParameters(): array
    {
        return [
            'id' => (string) $this->getId(),
            'slug' => $this->getSlug(),
        ];
    }

    /**
     * Test if a given user is main admin of the group.
     */
    public function isMainAdmin(User $user): bool
    {
        $mainAdminUserGroups = $this->userGroups->filter(
            static fn (UserGroup $userGroup) => $userGroup->getUser() === $user && $userGroup->isMainAdminAccount()
        );

        return !$mainAdminUserGroups->isEmpty();
    }

    /**
     * Get active offers only.
     *
     * @return Collection<int, GroupOffer>
     */
    public function getActiveOffers(): Collection
    {
        /** @var Collection<int, GroupOffer> $collection */
        $collection = $this->offers->filter(
            static fn (GroupOffer $groupOffer) => $groupOffer->isActive()
        );

        return $collection;
    }

    public function hasActiveOffers(): bool
    {
        return !$this->getActiveOffers()->isEmpty();
    }
}
