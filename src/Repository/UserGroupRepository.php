<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Group;
use App\Entity\UserGroup;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserGroup>
 */
class UserGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGroup::class);
    }

    public function save(UserGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Query<null, UserGroup>
     */
    public function getGroupMembers(Group $group, ?string $memberName): Query
    {
        $qb = $this
            ->createQueryBuilder('ug')
            ->andWhere('ug.group = :group')
            ->join('ug.user', 'u')
            ->orderBy('u.firstname', 'ASC')
            ->setParameter('group', $group);

        // filtered list by firstname, lastname or email member.
        if ($memberName !== null) {
            $qb
                ->andWhere(
                    'LOWER(u.email) LIKE LOWER(:memberName)
                     OR LOWER(u.firstname) LIKE LOWER(:memberName)
                     OR LOWER(u.lastname) LIKE LOWER(:memberName)'
                )
                ->setParameter('memberName', '%'.$memberName.'%');
        }

        /** @var Query<null, UserGroup> $query */
        $query = $qb->getQuery();

        return $query;
    }

    /**
     * @return Query<null, UserGroup>
     */
    public function getExpired(): Query
    {
        $today = Carbon::today();
        $qb = $this
            ->createQueryBuilder('ug')
            ->join('ug.user', 'u')
            ->join('ug.group', 'g')
            ->andWhere('ug.endAt < :date')
            ->setParameter('date', $today->format('Y-m-d'))
        ;

        /** @var Query<null, UserGroup> $query */
        $query = $qb->getQuery();

        return $query;
    }

    /**
     * Get all membership expiring in exactly x days.
     *
     * @return Query<null, UserGroup>
     */
    public function getExpiring(int $days): Query
    {
        $from = new \DateTimeImmutable(\sprintf('+%d days midnight', $days));
        $to = $from->modify('+ 1 day'); // just add one day for the end limit

        $qb = $this
            ->createQueryBuilder('ug')
            ->join('ug.user', 'u')
            ->join('ug.group', 'g')
            ->andWhere('ug.endAt >= :from')
            ->andWhere('ug.endAt < :to')
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
        ;

        /** @var Query<null, UserGroup> $query */
        $query = $qb->getQuery();

        return $query;
    }
}
