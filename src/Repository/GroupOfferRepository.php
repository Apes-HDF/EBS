<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GroupOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupOffer>
 *
 * @method GroupOffer|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupOffer|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupOffer[]    findAll()
 * @method GroupOffer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupOfferRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = GroupOffer::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    /**
     * Return an object or throws an exception if not found.
     */
    public function get(mixed $id, int|null $lockMode = null, int|null $lockVersion = null): GroupOffer
    {
        return $this->find($id, $lockMode, $lockVersion) ?? throw new \LogicException('Group offer not found.');
    }
}
