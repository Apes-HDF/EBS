<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductAvailability;
use App\Enum\Product\ProductAvailabilityMode;
use App\Enum\Product\ProductAvailabilityType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductAvailability>
 *
 * @method ProductAvailability|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductAvailability|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductAvailability[]    findAll()
 * @method ProductAvailability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class ProductAvailabilityRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = ProductAvailability::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    /**
     * Return product availability or throws an exception if not found.
     */
    public function get(mixed $id, int|null $lockMode = null, int|null $lockVersion = null): ProductAvailability
    {
        return $this->find($id, $lockMode, $lockVersion) ?? throw new \LogicException('ProductAvailability not found.');
    }

    public function save(ProductAvailability $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array<ProductAvailability>
     */
    public function getProductUnavailabilities(Product $product): array
    {
        /** @var ProductAvailability[] */
        return $this
            ->createQueryBuilder('a')
            ->andWhere('a.product = :product')
            ->andWhere('a.mode = :mode')
            ->andWhere('a.type = :type')
            ->andWhere('a.endAt > :today')
            ->orderBy('a.startAt')
            ->setParameters([
                'product' => $product,
                'mode' => ProductAvailabilityMode::UNAVAILABLE,
                'type' => ProductAvailabilityType::OWNER,
                'today' => date('Y-m-d'),
            ])
            ->getQuery()
            ->getResult();
    }

    public function deleteProductUnavailability(ProductAvailability $productAvailability): void
    {
        $this->getEntityManager()->remove($productAvailability);
        $this->getEntityManager()->flush();
    }
}
