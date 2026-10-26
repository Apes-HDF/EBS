<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
final class PaymentRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = Payment::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    public function get(mixed $id, ?int $lockMode = null, ?int $lockVersion = null): Payment
    {
        return $this->find($id, $lockMode, $lockVersion) ?? throw new \LogicException('Payment not found.');
    }
}
