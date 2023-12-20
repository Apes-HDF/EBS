<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\TimestampableEntity;
use App\Enum\Product\ProductAvailabilityMode;
use App\Enum\Product\ProductAvailabilityType;
use App\Repository\ProductAvailabilityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductAvailabilityRepository::class)]
#[ORM\Table]
#[ORM\Index(columns: ['product_id'])]
class ProductAvailability implements \Stringable
{
    use TimestampableEntity;

    /**
     * Generates a V6 uuid.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    /**
     * Linked product.
     */
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'availabilities')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] // if the product is deleted then the availability will also be deleted without constraint error
    private Product $product;

    /**
     * Optional service request for the type: "SERVICE_REQUEST".
     */
    #[ORM\ManyToOne(targetEntity: ServiceRequest::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private ?ServiceRequest $serviceRequest = null;

    /**
     * Availability or not. For now we only use the UNAVAILABLE type as we handle
     * a blacklist. All other days are considered available.
     */
    #[ORM\Column(name: 'type', type: 'string', nullable: false, enumType: ProductAvailabilityMode::class)]
    protected ProductAvailabilityMode $mode = ProductAvailabilityMode::UNAVAILABLE;

    /**
     * If availability if a custom one related to a given service request.
     */
    #[ORM\Column(name: 'mode', type: 'string', nullable: false, enumType: ProductAvailabilityType::class)]
    protected ProductAvailabilityType $type;

    /**
     * Start date of the period.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $startAt;

    /**
     * End date of the period, it can be equal to the starting date, in this case
     * the period is just one day.
     *
     * @todo endDate >= startDate
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $endAt;

    public function __toString(): string
    {
        return $this->product->getName().' / '.$this->startAt->format('Y-m-d').' / '.$this->endAt->format('Y-m-d');
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): ProductAvailability
    {
        $this->id = $id;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): ProductAvailability
    {
        $this->product = $product;

        return $this;
    }

    public function getServiceRequest(): ?ServiceRequest
    {
        return $this->serviceRequest;
    }

    public function setServiceRequest(?ServiceRequest $serviceRequest): ProductAvailability
    {
        $this->serviceRequest = $serviceRequest;

        return $this;
    }

    public function getMode(): ProductAvailabilityMode
    {
        return $this->mode;
    }

    public function setMode(ProductAvailabilityMode $mode): ProductAvailability
    {
        $this->mode = $mode;

        return $this;
    }

    public function getType(): ProductAvailabilityType
    {
        return $this->type;
    }

    public function setType(ProductAvailabilityType $type): ProductAvailability
    {
        $this->type = $type;

        return $this;
    }

    public function getStartAt(): \DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): ProductAvailability
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): \DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): ProductAvailability
    {
        $this->endAt = $endAt;

        return $this;
    }

    public static function productAvailabilityCreationByOwner(Product $product, \DateTimeImmutable $startAt, \DateTimeImmutable $endAt): ProductAvailability
    {
        $productAvailability = new self();

        return $productAvailability
            ->setProduct($product)
            ->setType(ProductAvailabilityType::OWNER)
            ->setStartAt($startAt)
            ->setEndAt($endAt);
    }
}
