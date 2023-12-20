<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Doctrine\Behavior\TimestampableEntity;
use App\Enum\Message\MessageType;
use App\Form\Type\User\ServiceRequest\NewMessageType;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message of a conversation. The "thread" is the request service and all messages
 * are therefore linked to this object.
 */
#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table]
#[ORM\Index(columns: ['service_request_id', 'created_at'])]
class Message implements \Stringable
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
     * Lender, it's the user that owns the object service. It can be retrieved from
     * the product, but it's easier to have here for the admin pages (filtering).
     */
    #[ORM\ManyToOne(targetEntity: ServiceRequest::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] // if the service request is deleted then the thread will also be deleted without constraint error
    #[Assert\NotBlank]
    protected ServiceRequest $serviceRequest;

    /**
     * Status of the request service, see full documentation in the enumeration class.
     * This status field is managed by a workflow and shouldn't never be changed
     * manually.
     */
    #[ORM\Column(type: 'string', nullable: false, enumType: MessageType::class)]
    #[Assert\NotBlank]
    protected MessageType $type;

    /**
     * The message translation code;.
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $messageTemplate = null;

    /**
     * Parameters for the message template.
     *
     * @var array<string, string>
     */
    #[ORM\Column(type: 'json')]
    protected array $messageParameters = [];

    /**
     * Final message content.
     */
    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotBlank(groups: [NewMessageType::class])]
    #[Assert\Length(max: 1000, groups: [NewMessageType::class])]
    protected string $message;

    /**
     * If the message was read by the owner.
     */
    #[ORM\Column]
    protected bool $ownerRead = false;

    /**
     * Date the message was read by the owner.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $ownerReadAt = null;

    /**
     * If the message was read by the recipient.
     */
    #[ORM\Column]
    protected bool $recipientRead = false;

    /**
     * Date the message was read by the owner.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $recipientReadAt = null;

    public function __toString(): string
    {
        return $this->message;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): Message
    {
        $this->id = $id;

        return $this;
    }

    public function getServiceRequest(): ServiceRequest
    {
        return $this->serviceRequest;
    }

    public function setServiceRequest(ServiceRequest $serviceRequest): Message
    {
        $this->serviceRequest = $serviceRequest;

        return $this;
    }

    public function getType(): MessageType
    {
        return $this->type;
    }

    public function setType(MessageType $type): Message
    {
        $this->type = $type;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): Message
    {
        $this->message = $message;

        return $this;
    }

    public function getMessageTemplate(): ?string
    {
        return $this->messageTemplate;
    }

    public function setMessageTemplate(?string $messageTemplate): Message
    {
        $this->messageTemplate = $messageTemplate;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getMessageParameters(): array
    {
        return $this->messageParameters;
    }

    /**
     * @param array<string, string> $messageParameters
     */
    public function setMessageParameters(array $messageParameters): Message
    {
        $this->messageParameters = $messageParameters;

        return $this;
    }

    public function isOwnerRead(): bool
    {
        return $this->ownerRead;
    }

    public function setOwnerRead(bool $ownerRead): Message
    {
        $this->ownerRead = $ownerRead;

        return $this;
    }

    public function getOwnerReadAt(): ?\DateTimeImmutable
    {
        return $this->ownerReadAt;
    }

    public function setOwnerReadAt(?\DateTimeImmutable $ownerReadAt): Message
    {
        $this->ownerReadAt = $ownerReadAt;

        return $this;
    }

    public function isRecipientRead(): bool
    {
        return $this->recipientRead;
    }

    public function setRecipientRead(bool $recipientRead): Message
    {
        $this->recipientRead = $recipientRead;

        return $this;
    }

    public function getRecipientReadAt(): ?\DateTimeImmutable
    {
        return $this->recipientReadAt;
    }

    public function setRecipientReadAt(?\DateTimeImmutable $recipientReadAt): Message
    {
        $this->recipientReadAt = $recipientReadAt;

        return $this;
    }

    // end of basic 'etters ————————————————————————————————————————————————————

    /**
     * Get the recipient of the message dependning on this type.
     */
    public function getSender(): User
    {
        if ($this->type->isFromOwner()) {
            return $this->serviceRequest->getOwner();
        }

        if ($this->type->isFromRecipient()) {
            return $this->serviceRequest->getRecipient();
        }

        throw new \LogicException('Cannot get recipient for a system message');
    }

    /**
     * Get the sender of the message depending on this type.
     */
    public function getRecipient(): User
    {
        if ($this->type->isFromOwner()) {
            return $this->serviceRequest->getRecipient();
        }

        if ($this->type->isFromRecipient()) {
            return $this->serviceRequest->getOwner();
        }

        throw new \LogicException('Cannot get recipient for a system message');
    }
}
