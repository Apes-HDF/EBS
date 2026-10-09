<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null findOneBySlug(string $slug)
 */
final class PageRepository extends ServiceEntityRepository
{
    private const ENTITY_CLASS = Page::class;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, self::ENTITY_CLASS);
    }

    public function getHome(): ?Page
    {
        return $this->findOneBy(['home' => true]);
    }
}
