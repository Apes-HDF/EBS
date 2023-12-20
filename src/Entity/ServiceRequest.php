<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Doctrine\Behavior\TimestampableEntity;
use App\Enum\ServiceRequest\ServiceRequestStatus;
use App\Form\Type\ServiceRequest\CreateServiceRequestType;
use App\Repository\ServiceRequestRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRequestRepository::class)]
#[ORM\Table]
#[ORM\Index(columns: ['owner_id', 'recipient_id'])]
#[AppAssert\Constraints\ServiceRequest\ProductAvailabilityNoOverlap(
    groups: [CreateServiceRequestType::class]
)]
class ServiceRequest implements \Stringable
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
    protected Uuid $id;

    /**
     * Lender/provider, it's the user that owns the product (object/service). It
     * can be retrieved from the product, but it's easier to have here for the admin
     * pages (filtering).
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] // if the lender is deleted then the service will also be deleted without constraint error
    #[Assert\NotBlank]
    protected User $owner;

    /**
     * Related product or service.
     */
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'serviceRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] // if the product is deleted then the service will also be deleted without constraint error
    #[Assert\NotBlank]
    protected Product $product;

    /**
     * Recipient, it's the borrower of the object or the recipient of the service.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] // if the borrower is deleted then the service will also be deleted without constraint error
    #[Assert\NotBlank]
    protected User $recipient;

    /**
     * Status of the request service, see full documentation in the enumeration class.
     * This status field is managed by a workflow and shouldn't never be changed
     * manually.
     */
    #[ORM\Column(type: 'string', nullable: false, enumType: ServiceRequestStatus::class)]
    #[Assert\NotBlank]
    protected ServiceRequestStatus $status = ServiceRequestStatus::NEW;

    /**
     * Planned starting date for the request service. When the user will retrieve
     * the object or date of the rendez-vous for the service.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $startAt;

    /**
     * Planned ending date for the request service: when the borrower will bring
     * back the oject of the owner or end date of the service. For a service, the
     * end date will be generally equal to the start at.
     * eg: the service is for one hour.
     *
     * @todo endDate should be >= startDate (can be the same day)
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $endAt;

    /**
     * Virtual field for the request service creation. This is the optional message
     * sent by the borrower to the lender a the request service creation. It is stored
     * in the conversation thread of the request service not the request service itself.
     */
    protected ?string $message = null;

    /**
     * @var Collection<int, Message> $messages
     */
    #[ORM\OneToMany(mappedBy: 'serviceRequest', targetEntity: Message::class, cascade: ['persist', 'remove', 'detach'])]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->id;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): ServiceRequest
    {
        $this->id = $id;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function isOwner(?User $user): bool
    {
        return $this->owner === $user;
    }

    public function setOwner(User $owner): ServiceRequest
    {
        $this->owner = $owner;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): ServiceRequest
    {
        $this->product = $product;

        return $this;
    }

    public function getRecipient(): User
    {
        return $this->recipient;
    }

    public function isRecipient(User $user): bool
    {
        return $this->recipient === $user;
    }

    public function setRecipient(User $recipient): ServiceRequest
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getStatus(): ServiceRequestStatus
    {
        return $this->status;
    }

    /**
     * This function should never be called manually, except in tests.
     *
     * @internal
     */
    public function setStatus(ServiceRequestStatus $status): ServiceRequest
    {
        $this->status = $status;

        return $this;
    }

    /**
     * This is needed for the workflow that works with strings, not enum.
     *
     * @internal
     */
    public function getStatusRaw(): string
    {
        return $this->status->value;
    }

    /**
     * This function should never be called manually.
     *
     * @internal
     */
    public function setStatusRaw(string $status): ServiceRequest
    {
        $this->status = ServiceRequestStatus::from($status);

        return $this;
    }

    public function getStartAt(): \DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): ServiceRequest
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): \DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): ServiceRequest
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): ServiceRequest
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @param Collection<int, Message> $messages
     */
    public function setMessages(Collection $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setServiceRequest($this);
        }

        return $this;
    }

    public function removeMessage(Message $item): self
    {
        $this->messages->removeElement($item);

        return $this;
    }

    public function messagesCount(): int
    {
        return $this->messages->count();
    }

    public function isLoan(): bool
    {
        return $this->product->getType()->isObject();
    }

    public function isService(): bool
    {
        return $this->product->getType()->isService();
    }

    // end of basic 'etters ————————————————————————————————————————————————————

    public function isOwnerOrRecipient(User $user): bool
    {
        return $this->owner === $user || $this->recipient === $user;
    }

    public function hasUnreadMessages(User $user): bool
    {
        $messages = $this->messages->filter(
            fn (Message $message) => $this->isOwner($user) ?
                    !$message->isOwnerRead() :
                    !$message->isRecipientRead());

        return !$messages->isEmpty();
    }

    /**
     * The the other user involved in the service request.
     */
    public function getOtherUser(?User $actor): User
    {
        return $this->isOwner($actor) ? $this->getRecipient() : $this->getOwner();
    }

    /**
     * Get the finalized date from the end date. We consider a service request is
     * finished the day just after the end date to avoid overlap problems.
     */
    public function getFinalizedAt(): \DateTimeImmutable
    {
        return $this->getEndAt()->modify('tomorrow midnight');
    }
}
