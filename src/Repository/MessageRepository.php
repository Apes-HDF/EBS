<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class MessageRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = Message::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    public function save(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function userHasNewMessage(User $user, bool $isOwner): bool
    {
        $qb = $this
            ->createQueryBuilder('m')
            ->join('m.serviceRequest', 'sr');

        if ($isOwner) {
            $qb->andWhere('m.ownerRead = false')
                ->andWhere('sr.owner = :user')
                ->setParameter('user', $user);
        } else {
            $qb
            ->andWhere('m.recipientRead = false')
            ->andWhere('sr.recipient = :user')
            ->setParameter('user', $user);
        }

        /** @var Message[] $unreadMessages */
        $unreadMessages = $qb->getQuery()->getResult();

        return \count($unreadMessages) !== 0;
    }
}
