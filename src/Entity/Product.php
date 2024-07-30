<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Controller\i18nTrait;
use App\Doctrine\Behavior\TimestampableEntity;
use App\Doctrine\Listener\ProductListener;
use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductType;
use App\Enum\Product\ProductVisibility;
use App\Form\Type\Product\AbstractProductFormType;
use App\Repository\ProductRepository;
use App\Security\Voter\ProductVoter;
use App\State\Processor\ProductSwitchProcessor;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table]
#[ORM\Index(columns: ['type', 'status', 'visibility', 'owner_id'])]
#[ORM\EntityListeners([ProductListener::class])]
#[ApiResource(
    operations: [
        new Patch(
            uriTemplate: '/product/{id}/switchStatus',
            openapiContext: ['summary' => 'Switch the status of the product'],
            normalizationContext: ['groups' => [ProductSwitchProcessor::class]],
            security: "is_granted('".ProductVoter::EDIT."', object)",
            input: false,
            processor: ProductSwitchProcessor::class,
        ),
    ]
)]
class Product implements \Stringable, ImagesInterface
{
    use TimestampableEntity;
    use ProductObjectTrait;
    use ProductServiceTrait;
    use i18nTrait;

    final public const DEFAULT_CURRENCY = 'EUR';

    /**
     * Generates a V6 uuid.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ApiProperty(identifier: true)]
    #[Groups([ProductSwitchProcessor::class])]
    private Uuid $id;

    /**
     * Type of the product. It can be an object to lend or a service.
     */
    #[ORM\Column(name: 'type', type: 'string', nullable: false, enumType: ProductType::class)]
    #[Assert\NotBlank]
    protected ProductType $type;

    /**
     * Main category of the product (1st or second level).
     */
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false)]
    #[Assert\NotBlank(groups: [AbstractProductFormType::class])]
    private Category $category;

    /**
     * Status of the product.
     */
    #[ORM\Column(name: 'status', type: 'string', nullable: false, enumType: ProductStatus::class)]
    #[Assert\NotBlank]
    #[Groups([ProductSwitchProcessor::class])]
    protected ProductStatus $status;

    /**
     * Visibility of the product.
     */
    #[ORM\Column(name: 'visibility', type: 'string', nullable: false, enumType: ProductVisibility::class)]
    #[Assert\NotBlank]
    protected ProductVisibility $visibility = ProductVisibility::PUBLIC;

    /**
     * User that owns the product or propose the service.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] // if the owner is deleted then the product will also be deleted without constraint error
    #[Assert\NotBlank]
    protected User $owner;

    /**
     * Short and main name of the product.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank(groups: [AbstractProductFormType::class])]
    #[Assert\Length(max: 255, groups: [AbstractProductFormType::class, 'default'])]
    private string $name;

    /**
     * SEO friendly name for URLs.
     */
    #[ORM\Column(length: 255, unique: false)]
    #[Gedmo\Slug(fields: ['name'])]
    private string $slug;

    /**
     * Longer description of the product.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(groups: [AbstractProductFormType::class])]
    #[Assert\Length(max: 2000, groups: [AbstractProductFormType::class])]
    private ?string $description = null;

    /**
     * User images for the product.
     *
     * @var array<string>
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $images = null;

    /**
     * @var Collection<int, ProductAvailability> $availabilities
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductAvailability::class, cascade: ['persist', 'remove', 'detach'])]
    #[ORM\OrderBy(['startAt' => 'ASC'])]
    private Collection $availabilities;

    /**
     * @var Collection<int, ServiceRequest> $serviceRequests
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ServiceRequest::class)]
    private Collection $serviceRequests;

    /**
     * If the product in not public then the list of group the product is visible.
     *
     * @var Collection<int, Group> $groups
     */
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'products')]
    #[Assert\When(
        expression: '!this.getVisibility().isPublic() && !this.getOwner().getUserGroupsConfirmedWithServices().isEmpty()',
        constraints: [
            new Assert\Count(min: 1, minMessage: 'app.entity.product.groups.constraints.count.min_message'),
        ],
        groups: [AbstractProductFormType::class],
    )]
    private Collection $groups;

    /**
     * This is a virtual field to store the distance with a given location when
     * using a proximity filter.
     */
    private ?int $geoDistance = null;

    /**
     * Get distance in meters.
     */
    public function setGeoDistance(?int $geoDistance): self
    {
        $this->geoDistance = $geoDistance;

        return $this;
    }

    /**
     * Return kilometers.
     */
    public function getGeoDistance(): ?float
    {
        return $this->geoDistance !== null ? $this->geoDistance / 1000 : null;
    }

    public function __construct()
    {
        $this->availabilities = new ArrayCollection();
        $this->serviceRequests = new ArrayCollection();
        $this->groups = new ArrayCollection();
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

    public function getType(): ProductType
    {
        return $this->type;
    }

    public function setType(ProductType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    public function setStatus(ProductStatus $status): Product
    {
        $this->status = $status;

        return $this;
    }

    public function getVisibility(): ProductVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(ProductVisibility $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function isOwner(User $user): bool
    {
        return $this->owner === $user;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function getFirstImage(): ?string
    {
        return $this->images[0] ?? null;
    }

    /**
     * @param array<string|null>|null $images
     */
    public function setImages(?array $images): self
    {
        $this->images = array_values(array_filter($images ?? [])); // make sure we don't save null or empty values

        return $this;
    }

    /**
     * @param array<string> $images
     */
    public function addImages(array $images): self
    {
        $this->images = array_merge($this->images ?? [], $images);

        return $this;
    }

    /**
     * @return Collection<int, ProductAvailability>
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    /**
     * @param Collection<int, ProductAvailability> $availabilities
     */
    public function setAvailabilities(Collection $availabilities): self
    {
        $this->availabilities = $availabilities;

        return $this;
    }

    public function addAvailability(ProductAvailability $productAvailability): self
    {
        if (!$this->availabilities->contains($productAvailability)) {
            $this->availabilities->add($productAvailability);
            $productAvailability->setProduct($this);
        }

        return $this;
    }

    public function removeAvailability(ProductAvailability $productAvailability): self
    {
        $this->availabilities->removeElement($productAvailability);

        return $this;
    }

    /**
     * @return Collection<int, ServiceRequest>
     */
    public function getServiceRequests(): Collection
    {
        return $this->serviceRequests;
    }

    /**
     * @param Collection<int, ServiceRequest> $serviceRequests
     */
    public function setServiceRequests(Collection $serviceRequests): void
    {
        $this->serviceRequests = $serviceRequests;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @return array<int, string>
     */
    public function getGroupsIds(): array
    {
        return $this->getGroups()->map(fn (Group $group) => (string) $group->getId())->toArray();
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * Remove all associated groups.
     */
    public function removeGroups(): self
    {
        $this->groups->clear();

        return $this;
    }

    public function setPublic(): self
    {
        $this->visibility = ProductVisibility::PUBLIC;

        return $this;
    }

    public function setPaused(): self
    {
        $this->status = ProductStatus::PAUSED;

        return $this;
    }

    public function setActive(): self
    {
        $this->status = ProductStatus::ACTIVE;

        return $this;
    }

    /* End of basic getters/setters ========================================= */

    public function isActive(): bool
    {
        return $this->getStatus()->isActive();
    }

    public function isPaused(): bool
    {
        return $this->getStatus()->isPaused();
    }

    public function switchStatus(): self
    {
        $this->setStatus($this->isActive() ? ProductStatus::PAUSED : ProductStatus::ACTIVE);

        return $this;
    }

    /**
     * Return the unavailabilities of the product as a simple array of dates "2023-02-09".
     *
     * @return array<string>
     */
    public function getUnavailabilities(?ServiceRequest $serviceRequest = null): array
    {
        /** @var array<Carbon> $resultArray */
        $resultArray = [];
        $today = CarbonImmutable::today(); // start of day 00:00:00
        $unavailabilities = $this->getAvailabilities()->filter(
            fn (ProductAvailability $pa) => $pa->getMode()->isUnavailable() && // of the good type
                ($serviceRequest === null || $pa->getServiceRequest() !== $serviceRequest) && // exclude the dates of the current service request (modify dates)
                $pa->getEndAt() >= $today // passed dates are useless but the start date can be in the past
        );

        foreach ($unavailabilities as $unavailability) {
            /** @var ProductAvailability $unavailability */
            $period = CarbonInterval::days()->toPeriod($unavailability->getStartAt(), $unavailability->getEndAt());
            $resultArray = array_merge($resultArray, $period->toArray());
        }
        $resultArray = array_map(static fn (CarbonInterface $date) => $date->format('Y-m-d'), $resultArray);
        sort($resultArray);

        return array_unique($resultArray);
    }

    /**
     * Get 1st level category.
     */
    public function getMainCategory(): Category
    {
        return $this->category->getParent() ?? $this->category;
    }

    /**
     * Get subcategory, it is the current category if it is a child.
     */
    public function getSubCategory(): ?Category
    {
        return $this->category->hasParent() ? $this->category : null;
    }

    public function createServiceRequest(User $recipient, \DateTimeImmutable $startAt, \DateTimeImmutable $endAt): ServiceRequest
    {
        return (new ServiceRequest())
            ->setOwner($this->getOwner())
            ->setProduct($this)
            ->setRecipient($recipient)
            ->setStartAt($startAt)
            ->setEndAt($endAt);
    }

    public function duplicate(): self
    {
        return (new Product())
            ->setType($this->getType())
            ->setCategory($this->getCategory())
            ->setOwner($this->getOwner())
            ->setType($this->getType())
            ->setStatus($this->getStatus())
            ->setVisibility($this->getVisibility())
            ->setDescription($this->getDescription())
            ->setAge($this->getAge())
            ->setDeposit($this->getDeposit())
            ->setCurrency($this->getCurrency())
            ->setPreferredLoanDuration($this->getPreferredLoanDuration())
            ->setDuration($this->getDuration());
    }

    public function deleteImage(string $image): self
    {
        $images = array_flip($this->images ?? []);
        unset($images[$image]);
        $this->images = array_values(array_flip($images));

        return $this;
    }

    public function delete(): self
    {
        $this->status = ProductStatus::DELETED;

        return $this;
    }

    public function hasServiceRequests(): bool
    {
        return !$this->serviceRequests->isEmpty();
    }

    public function hasOngoingServiceRequests(): bool
    {
        $ongoing = $this->serviceRequests->filter(
            fn (ServiceRequest $serviceRequest) => $serviceRequest->getStatus()->isOngoing()
        );

        return !$ongoing->isEmpty();
    }

    /**
     * A product is indexable if it is active and the owner has not activated the
     * vacation mode.
     */
    public function isIndexable(): bool
    {
        return $this->status->isIndexable()
            && $this->owner->isIndexable()
        ;
    }

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
}
