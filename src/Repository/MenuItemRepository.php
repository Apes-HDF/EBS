<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Enum\Menu\LinkType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

/**
 * @method MenuItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuItem[]    findAll()
 * @method MenuItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class MenuItemRepository extends SortableRepository
{
    private const ENTITY_CLASS = MenuItem::class;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(self::ENTITY_CLASS));
    }

    public function save(MenuItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MenuItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return MenuItem[]
     */
    public function findFirstLevelMenuLinks(string $code): array
    {
        /** @var MenuItem[] */
        return $this
            ->createQueryBuilder('i')
            ->andWhere('i.parent is null')
            ->join('i.menu', 'm', 'WITH', 'm.code = :code')
            ->setParameter('code', $code)
            ->orderBy('i.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MenuItem[]
     */
    public function getFooterItems(string $linkType): array
    {
        /** @var MenuItem[] */
        return $this
            ->createQueryBuilder('i')
            ->andWhere('i.linkType = :linkType')
            ->join('i.menu', 'm', 'WITH', 'm.code = :code')
            ->setParameters([
                'code' => Menu::FOOTER,
                'linkType' => $linkType,
            ])
            ->orderBy('i.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getLinksByCode(QueryBuilder $qb, string $code): QueryBuilder
    {
        return $qb
            ->join('entity.menu', 'm', 'WITH', 'm.code = :code')
            ->andWhere('entity.linkType = :linkType')
            ->andWhere('entity.parent is null')
            ->setParameters([
                'code' => $code,
                'linkType' => LinkType::LINK->value,
            ])
        ;
    }
}
