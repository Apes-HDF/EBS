<?php

declare(strict_types=1);

namespace App\Controller\Payment;

use App\Entity\GroupOffer;
use App\Repository\GroupOfferRepository;
use Symfony\Component\Uid\Uuid;

/**
 * @property GroupOfferRepository $groupOfferRepository
 */
trait GroupOfferTrait
{
    public function getGroupOffer(string $id): GroupOffer
    {
        $groupOffer = $this->groupOfferRepository->find(Uuid::fromString($id));
        if ($groupOffer === null || !$groupOffer->isActive()) {
            throw $this->createNotFoundException('Group offer not found');
        }

        return $groupOffer;
    }
}
