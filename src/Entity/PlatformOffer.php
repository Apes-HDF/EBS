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
}
