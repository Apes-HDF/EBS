<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ServiceRequest;
use App\Entity\User;
use App\Enum\ServiceRequest\ServiceRequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceRequest>
 *
 * @method ServiceRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceRequest[]    findAll()
 * @method ServiceRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ServiceRequestRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = ServiceRequest::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    /**
     * Return an object or throws an exception if not found.
     */
    public function get(mixed $id, int|null $lockMode = null, int|null $lockVersion = null): ServiceRequest
    {
        return $this->find($id, $lockMode, $lockVersion) ?? throw new \LogicException('Service request not found.');
    }

    public function save(ServiceRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServiceRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array<Product>|ArrayCollection<int, Product>|null $products
     */
    public function getLendings(User $owner, mixed $products): Query
    {
        $qb = $this
            ->createQueryBuilder('sr')
            ->leftJoin('sr.messages', 'm')
            ->andWhere('sr.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('sr.createdAt', 'DESC');

        if ($products !== null && \count($products) !== 0) {
            $qb->andWhere('sr.product IN (:products)')->setParameter('products', $products);
        }

        return $qb->getQuery();
    }

    /**
     * @param array<Product>|ArrayCollection<int, Product>|null $products
     */
    public function getLoans(User $recipient, mixed $products): Query
    {
        $qb = $this
            ->createQueryBuilder('sr')
            ->leftJoin('sr.messages', 'm')
            ->andWhere('sr.recipient = :recipient')
            ->setParameter('recipient', $recipient)
            ->orderBy('sr.createdAt', 'DESC');

        if ($products !== null && \count($products) !== 0) {
            $qb->andWhere('sr.product IN (:products)')->setParameter('products', $products);
        }

        return $qb->getQuery();
    }

    /**
     * Get all items having a property set to a given date interval (a day).
     */
    public function getActionSoon(string $property, int $days = 1): Query
    {
        $from = new \DateTimeImmutable(sprintf('+%d days midnight', $days));
        $to = $from->modify('+ 1 day'); // just add one day for the end limit

        $qb = $this
            ->createQueryBuilder('sr')
            ->innerJoin('sr.owner', 'o')
            ->innerJoin('sr.recipient', 'g')
            ->andWhere(sprintf('sr.%s >= :from', $property))
            ->andWhere(sprintf('sr.%s < :to', $property))
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->andWhere('sr.status = :status')
            ->setParameter('status', ServiceRequestStatus::CONFIRMED)
        ;

        return $qb->getQuery();
    }

    public function getStartingAtTomorow(): Query
    {
        return $this->getActionSoon('startAt');
    }

    public function getEndingAtTomorow(): Query
    {
        return $this->getActionSoon('endAt');
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNewServiceRequestsOfMonth(): int
    {
        $today = new \DateTime();

        /** @var int */
        return $this
            ->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.createdAt >= :firstDay')
            ->andWhere('sr.createdAt <= :lastDay')
            ->setParameters([
                'firstDay' => $today->modify('first day of this month')->format('Y-m-d'),
                'lastDay' => $today->modify('last day of this month')->format('Y-m-d'),
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
