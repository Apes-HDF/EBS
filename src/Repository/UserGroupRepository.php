<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Enum\Group\UserMembership;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserGroup>
 *
 * @method UserGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGroup[]    findAll()
 * @method UserGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * Ids (RFC 4122) of the groups administrated by the user, read from the
     * database: not affected by a membership being modified in the current request.
     *
     * @return list<string>
     */
    public function getAdministratedGroupIds(User $user): array
    {
        /** @var list<array{groupId: string}> $rows */
        $rows = $this->createQueryBuilder('ug')
            ->select('IDENTITY(ug.group) AS groupId')
            ->andWhere('ug.user = :user')
            ->andWhere('ug.membership = :admin OR ug.mainAdminAccount = true')
            ->setParameter('user', $user->getId(), 'uuid')
            ->setParameter('admin', UserMembership::ADMIN)
            ->getQuery()
            ->getScalarResult();

        return array_map(static fn (array $row): string => $row['groupId'], $rows);
    }

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

        return $qb->getQuery();
    }

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

        return $qb->getQuery();
    }

    /**
     * Get all membership expiring in exactly x days.
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

        return $qb->getQuery();
    }
}
