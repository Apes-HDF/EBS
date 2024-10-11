<?php

declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\Behavior\AbstractOfferEntity;
use App\Repository\GroupOfferRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GroupOfferRepository::class)]
#[ORM\Table(name: 'group_offer')]
#[ORM\Index(columns: ['type'])]
class GroupOffer extends AbstractOfferEntity
{
    /**
     * Related group.
     */
    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Group $group;

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): self
    {
        $this->group = $group;

        return $this;
    }
}
