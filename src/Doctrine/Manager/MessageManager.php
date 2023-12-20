<?php

declare(strict_types=1);

namespace App\Doctrine\Manager;

use App\Controller\i18nTrait;
use App\Entity\Message;
use App\Entity\ServiceRequest;
use App\Enum\Message\MessageType;
use App\Repository\MessageRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MessageManager
{
    use i18nTrait;

    public const DOMAIN = 'messages_system';

    public function __construct(
        private readonly MessageRepository $messageRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function save(Message $entity, bool $flush = false): void
    {
        $this->messageRepository->save($entity, $flush);
    }

    /**
     * @param array<string,string|null> $parameters
     */
    public function getSystemMessage(string $id, array $parameters): string
    {
        return $this->translator->trans($this->getI18nPrefix().'.'.$id, $parameters, self::DOMAIN);
    }

    /**
     * @param array<string,string> $messageParameters
     */
    public function createSystemMessage(
        ServiceRequest $serviceRequest,
        string $messageTemplate,
        array $messageParameters = [],
        \DateTimeImmutable $createdAt = null,
    ): Message {
        $message = (new Message())
            ->setServiceRequest($serviceRequest)
            ->setType(MessageType::SYSTEM)
            ->setMessageTemplate($messageTemplate)
            ->setMessageParameters($messageParameters)
            ->setMessage($this->getSystemMessage($messageTemplate, $messageParameters));

        // allow to force Timestampable dates
        if ($createdAt !== null) {
            $message->setCreatedAt($createdAt)
                ->setUpdatedAt($createdAt);
        }

        return $message;
    }

    public function createFromRecipientMessage(ServiceRequest $serviceRequest, string $message, \DateTimeImmutable $createdAt = null): Message
    {
        $message = (new Message())
            ->setServiceRequest($serviceRequest)
            ->setType(MessageType::FROM_RECIPIENT)
            ->setMessage($message)
        ;

        // allow to force Timestampable dates
        if ($createdAt !== null) {
            $message->setCreatedAt($createdAt)
                ->setUpdatedAt($createdAt);
        }

        return $message;
    }

    public function createFromOwnerMessage(ServiceRequest $serviceRequest, string $message): Message
    {
        return (new Message())
            ->setServiceRequest($serviceRequest)
            ->setType(MessageType::FROM_OWNER)
            ->setMessage($message);
    }
}
