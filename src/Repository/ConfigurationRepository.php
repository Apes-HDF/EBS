<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Configuration;
use App\Enum\ConfigurationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConfigurationRepository>
 *
 * @method Configuration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Configuration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Configuration[]    findAll()
 * @method Configuration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ConfigurationRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = Configuration::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    public function save(Configuration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Configuration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getInstanceConfiguration(): ?Configuration
    {
        return $this->findOneBy(['type' => ConfigurationType::INSTANCE]);
    }

    public function getInstanceConfigurationOrCreate(): Configuration
    {
        $cfg = $this->getInstanceConfiguration();
        if (!$cfg instanceof Configuration) {
            $cfg = Configuration::getInstanceConfiguration();
        }

        return $cfg;
    }

    public function getServicesParameter(): bool
    {
        /** @var array{configuration: array{ services: array{ servicesEnabled: bool }}} $config */
        $config = $this
            ->createQueryBuilder('c')
            ->select('c.configuration')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $config['configuration']['services']['servicesEnabled'];
    }
}
