<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'migration_versions')]
#[ORM\Entity]
class MigrationVersions
{
    #[ORM\Column(name: 'version', type: 'string', length: 191, nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $version;

    #[ORM\Column(name: 'executed_at', type: 'datetime', nullable: true)]
    private ?\DateTime $executedAt;

    #[ORM\Column(name: 'execution_time', type: 'integer', nullable: true)]
    private ?int $executionTime;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): MigrationVersions
    {
        $this->version = $version;

        return $this;
    }

    public function getExecutedAt(): ?\DateTime
    {
        return $this->executedAt;
    }

    public function setExecutedAt(?\DateTime $executedAt): MigrationVersions
    {
        $this->executedAt = $executedAt;

        return $this;
    }

    public function getExecutionTime(): ?int
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?int $executionTime): MigrationVersions
    {
        $this->executionTime = $executionTime;

        return $this;
    }
}
