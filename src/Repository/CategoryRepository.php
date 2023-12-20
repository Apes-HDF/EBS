<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\Product\ProductType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * #@extends ServiceEntityRepository<Category>.
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class CategoryRepository extends NestedTreeRepository implements ServiceEntityRepositoryInterface
{
    private const ENTITY_CLASS = Category::class;

    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass(self::ENTITY_CLASS);
        parent::__construct($manager, $manager->getClassMetadata(self::ENTITY_CLASS));
    }

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function addTypeFilter(QueryBuilder $qb, ProductType $productType): QueryBuilder
    {
        return $qb
            ->andWhere('entity.type = :type')
            ->setParameter(':type', $productType);
    }

    /**
     * Get the category hierarchy thanks to the tree behaviour. Ordering by the
     * left index is the easiest way to do the job.
     */
    public function getHierarchy(?ProductType $type = null, ?User $user = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.enabled = true')
            ->addOrderBy('c.lft', 'ASC');

        if ($type !== null) {
            $qb
                ->andWhere('c.type = :productType')
                ->setParameter('productType', $type)
            ;
        }

        if ($user !== null) {
            $qb->from(Product::class, 'p')
                ->andWhere('p.category = c')
                ->andWhere('p.owner = :user')
                ->setParameter('user', $user)
            ;
        }

        return $qb;
    }
}
