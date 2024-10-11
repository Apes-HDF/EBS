<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\AbstractOfferEntity;
use App\Repository\PlatformOfferRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlatformOfferRepository::class)]
#[ORM\Table(name: 'platform_offer')]
#[ORM\Index(columns: ['type'])]
class PlatformOffer extends AbstractOfferEntity
{
    /**
     * Related platform.
     */
    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Configuration $configuration;

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(?Configuration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}
