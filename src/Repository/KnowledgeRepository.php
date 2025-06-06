<?php

namespace App\Repository;

use App\Entity\Knowledge;
use App\Entity\KnowledgeCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Knowledge>
 */
class KnowledgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Knowledge::class);
    }

    public function search(string $query, ?string $category = null, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('k')
            ->leftJoin('k.category', 'c')
            ->addSelect('c');

        // Search in title, content, and keywords
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like('LOWER(k.title)', ':query'),
                $qb->expr()->like('LOWER(k.content)', ':query'),
                $qb->expr()->like('LOWER(k.keywords)', ':query')
            )
        );

        if ($category && $category !== 'all') {
            $qb->andWhere('c.id = :category')
               ->setParameter('category', $category);
        }

        $qb->setParameter('query', '%' . strtolower($query) . '%')
           ->orderBy('k.views', 'DESC')
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findByCategory(string $categoryId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $items = $this->createQueryBuilder('k')
            ->leftJoin('k.category', 'c')
            ->addSelect('c')
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('k.title', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        $total = $this->createQueryBuilder('k')
            ->select('COUNT(k.id)')
            ->leftJoin('k.category', 'c')
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    public function findByCategoryAndId(string $categoryId, string $itemId): ?Knowledge
    {
        return $this->createQueryBuilder('k')
            ->leftJoin('k.category', 'c')
            ->addSelect('c')
            ->leftJoin('k.relatedTo', 'r')
            ->addSelect('r')
            ->where('c.id = :categoryId')
            ->andWhere('k.id = :itemId')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('itemId', $itemId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPopular(int $limit = 10): array
    {
        return $this->createQueryBuilder('k')
            ->leftJoin('k.category', 'c')
            ->addSelect('c')
            ->orderBy('k.views', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('k')
            ->leftJoin('k.category', 'c')
            ->addSelect('c')
            ->orderBy('k.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function incrementViews(string $id): void
    {
        $this->createQueryBuilder('k')
            ->update()
            ->set('k.views', 'k.views + 1')
            ->where('k.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
} 