<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Enum\User\UserType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User|null findOneByEmail(string $email)
 * @method User|null findOneByConfirmationToken(string $token)
 * @method User|null findOneByLostPasswordToken(string $token)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Return object or throws an exception if not found.
     */
    public function get(mixed $id, int|null $lockMode = null, int|null $lockVersion = null): User
    {
        return $this->find($id, $lockMode, $lockVersion) ?? throw new \LogicException('User not found.');
    }

    /**
     * Use the UserManager instead.
     */
    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Use the UserManager instead.
     */
    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->save($user, true);
    }

    /**
     * This rules are duplicated with ProductRepository::getIndexable. Check to
     * factorize this.
     *
     * @see ProductRepository::getIndexable
     */
    public function getPlacesQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.type = :type')
            ->setParameter('type', UserType::PLACE)

            // enabled and confirmed accounts
            ->andWhere('p.enabled = :enabled')
            ->setParameter(':enabled', true)
            ->andWhere('p.emailConfirmed = :emailConfirmed')
            ->setParameter(':emailConfirmed', true)

            // vacation mode is not enabled
            ->andWhere('p.vacationMode = :vacationMode')
            ->setParameter(':vacationMode', false)

            // only places with a valid address
            ->andWhere('p.address is not null')

            // sort
            ->orderBy('p.name', 'ASC')
        ;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getUserCountByType(UserType $type): int
    {
        /** @var int */
        return $this
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNewUsersOfMonthByType(UserType $type): int
    {
        $today = new \DateTime();

        /** @var int */
        return $this
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.type = :type')
            ->andWhere('u.createdAt >= :firstDay')
            ->andWhere('u.createdAt <= :lastDay')
            ->setParameters([
                'firstDay' => $today->modify('first day of this month')->format('Y-m-d'),
                'lastDay' => $today->modify('last day of this month')->format('Y-m-d'),
                'type' => $type,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
