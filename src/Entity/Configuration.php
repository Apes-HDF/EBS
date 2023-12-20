<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\TimestampableEntity;
use App\Enum\ConfigurationType;
use App\Message\Command\Admin\ParametersFormCommand;
use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'type', type: 'string', nullable: false, enumType: ConfigurationType::class)]
    #[Assert\NotBlank]
    protected ConfigurationType $type = ConfigurationType::INSTANCE;

    /**
     * Associative array to store parameters.
     *
     * @var array<string, array<string,mixed>>
     */
    #[ORM\Column(type: 'json')]
    private array $configuration = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ConfigurationType
    {
        return $this->type;
    }

    public function setType(ConfigurationType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array<string, array<string,mixed>>
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @param array<string, array<string,mixed>> $configuration
     */
    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    public static function getInstanceConfiguration(): Configuration
    {
        return (new self())->setType(ConfigurationType::INSTANCE);
    }

    /** end of basic getters and setters ------------------------------------------------ */

    /**
     * @return array<string, string>
     */
    public function getNotificationsSender(): array
    {
        /** @var array<string, string> $notificationsSender */
        $notificationsSender = $this->configuration['notificationsSender'] ?? [];

        return $notificationsSender;
    }

    public function getNotificationsSenderEmail(): string
    {
        $notificationsSender = $this->getNotificationsSender();

        return $notificationsSender['notificationsSenderEmail'] ?? '';
    }

    public function getNotificationsSenderName(): string
    {
        $notificationsSender = $this->getNotificationsSender();

        return $notificationsSender['notificationsSenderName'] ?? '';
    }

    /**
     * @return array<mixed>
     */
    public function getContactInformations(): array
    {
        /** @var array<mixed> $contactInfo */
        $contactInfo = $this->configuration['contact'] ?? [];

        return $contactInfo;
    }

    public function getContactEnabled(): bool
    {
        /** @var array<bool> $contactEnabled */
        $contactEnabled = $this->getContactInformations();

        return $contactEnabled['contactFormEnabled'];
    }

    public function getContactEmail(): string
    {
        /** @var array<string> $contactEnabled
         */
        $contactEnabled = $this->getContactInformations();

        return $contactEnabled['contactFormEmail'];
    }

    public function isConversationAdminAccessible(): bool
    {
        /** @var array<bool> $config */
        $config = $this->configuration['confidentiality'] ?? [];

        return $config['confidentialityConversationAdminAccess'];
    }

    public function isGroupsCreationForAll(): bool
    {
        return $this->configuration['groups']['groupsCreationMode'] === ParametersFormCommand::ALL;
    }

    // for test only
    public function setGroupsCreationModeToAdminOnly(): self
    {
        $this->configuration['groups']['groupsCreationMode'] = ParametersFormCommand::ONLY_ADMIN;

        return $this;
    }
}
