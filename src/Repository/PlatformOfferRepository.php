<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PlatformOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlatformOffer>
 *
 * @method PlatformOffer|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlatformOffer|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlatformOffer[]    findAll()
 * @method PlatformOffer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlatformOfferRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = PlatformOffer::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    /**
     * Return an object or throws an exception if not found.
     */
    public function get(mixed $id, int|null $lockMode = null, int|null $lockVersion = null): PlatformOffer
    {
        return $this->find($id, $lockMode, $lockVersion) ?? throw new \LogicException('Platform offer not found.');
    }

    public function findOneActive(string $id): ?PlatformOffer
    {
        return $this->findOneBy(['id' => $id, 'active' => true]);
    }
}
