<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Group;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\Product\ProductStatus;
use App\Enum\Product\ProductType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = Product::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    /**
     * Return an object or throws an exception if not found.
     */
    public function get(mixed $id, ?int $lockMode = null, ?int $lockVersion = null): Product
    {
        return $this->find($id, $lockMode, $lockVersion) ?? throw new \LogicException('Product not found.');
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getUserProductsByType(User $user, ?ProductType $type, ?Uuid $category, ?Group $group): Query
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->andWhere('p.owner = :user')
            ->andWhere('p.status != :status')
            ->setParameters([
                'user' => $user,
                'status' => ProductStatus::DELETED,
            ]);

        if ($type !== null) {
            $qb->andWhere('p.type = :type')
                ->setParameter('type', $type)
            ;
        }

        if ($category !== null) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $category)
            ;
        }

        if ($group !== null) {
            $qb->innerJoin('p.groups', 'g')
                ->andWhere('g.id = :group')
                ->setParameter('group', $group)
            ;
        }

        return $qb->getQuery();
    }

    /**
     * Business rules for searchable/indexable products.
     */
    public function getIndexable(?ProductType $type = null): Query
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->innerJoin('p.owner', 'owner')

            // enabled and confirmed accounts
            ->andWhere('owner.enabled = :enabled')
            ->setParameter(':enabled', true)
            ->andWhere('owner.emailConfirmed = :emailConfirmed')
            ->setParameter(':emailConfirmed', true)

            // vacation mode is not enabled for owner
            ->andWhere('owner.vacationMode = :vacationMode')
            ->setParameter(':vacationMode', false)

            // active products
            ->andWhere('p.status = :status')
            ->setParameter(':status', ProductStatus::ACTIVE)

            // alpha sort
            ->orderBy('p.name', 'ASC')
        ;

        if ($type !== null) {
            $qb->andWhere('p.type = :type')
                ->setParameter(':type', $type);
        }

        return $qb->getQuery();
    }

    public function getObjects(): Query
    {
        return $this->getIndexable(ProductType::OBJECT);
    }

    public function getServices(): Query
    {
        return $this->getIndexable(ProductType::SERVICE);
    }
}
