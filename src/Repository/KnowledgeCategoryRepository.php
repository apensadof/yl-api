<?php

namespace App\Repository;

use App\Entity\KnowledgeCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<KnowledgeCategory>
 */
class KnowledgeCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KnowledgeCategory::class);
    }

    public function findAllWithCount(): array
    {
        return $this->createQueryBuilder('kc')
            ->select('kc', 'COUNT(k.id) as itemCount')
            ->leftJoin('kc.knowledgeItems', 'k')
            ->groupBy('kc.id')
            ->orderBy('kc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function updateItemCount(string $categoryId): void
    {
        $count = $this->createQueryBuilder('kc')
            ->select('COUNT(k.id)')
            ->leftJoin('kc.knowledgeItems', 'k')
            ->where('kc.id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getSingleScalarResult();

        $this->createQueryBuilder('kc')
            ->update()
            ->set('kc.itemCount', ':count')
            ->where('kc.id = :categoryId')
            ->setParameter('count', $count)
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->execute();
    }
} 