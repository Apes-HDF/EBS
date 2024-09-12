<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Enum\Group\GroupType;
use App\Enum\Group\UserMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Group>
 *
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class GroupRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = Group::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    /**
     * Return an object or throws an exception if not found.
     */
    public function get(mixed $id, ?int $lockMode = null, ?int $lockVersion = null): Group
    {
        return $this->find($id, $lockMode, $lockVersion) ?? throw new \LogicException('Group not found.');
    }

    public function save(Group $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Group $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get visible groups for a given user.
     */
    public function getGroups(?string $groupName): Query
    {
        $qb = $this
            ->createQueryBuilder('g')

            // public
            ->andWhere('g.type = :type')
            ->setParameter(':type', GroupType::PUBLIC);

        // @todo or member of the private group

        // filter list by group name
        if ($groupName !== null) {
            $qb->andWhere('LOWER(g.name) LIKE LOWER(:groupName)')->setParameter('groupName', '%'.$groupName.'%');
        }

        // alpha sort
        return $qb->orderBy('g.name', 'ASC')->getQuery();
    }

    public function getUserGroupsWithEnabledServices(User $user): QueryBuilder
    {
        return $this->getUserGroups($user)
            ->andWhere('g.servicesEnabled = :enabled')
            ->setParameter('enabled', true);
    }

    /**
     * @return Group[]
     */
    public function getGroupsByEnabledServices(bool $servicesEnabled, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('g')
            ->andWhere('g.servicesEnabled = :servicesEnabled')
            ->setParameter('servicesEnabled', $servicesEnabled);

        if ($user instanceof User) {
            $qb
                ->leftJoin('g.userGroups', 'gu')
                ->andWhere('gu.user = :user')
                ->andWhere('gu.mainAdminAccount = :mainAdminAccount OR gu.membership = :membership')
                ->setParameter('user', $user)
                ->setParameter('mainAdminAccount', true)
                ->setParameter('membership', UserMembership::ADMIN);
        }

        /** @var Group[] */
        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Group[] $groups
     */
    public function disableServicesForAllGroups(array $groups): void
    {
        foreach ($groups as $group) {
            $group->setServicesEnabled(false);
            $this->getEntityManager()->persist($group);
        }
        $this->getEntityManager()->flush();
    }

    public function disableServicesForChildGroup(Group $group): void
    {
        /** @var Group $child */
        foreach ($group->getChildren() as $child) {
            $child->setServicesEnabled(false);
            $this->getEntityManager()->persist($child);
        }
        $this->getEntityManager()->flush();
    }

    public function getUserGroups(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('g')
            ->from(UserGroup::class, 'ug')
            ->andWhere('g = ug.group')
            ->andWhere('ug.user = :user')
            ->setParameter('user', $user);
    }
}
