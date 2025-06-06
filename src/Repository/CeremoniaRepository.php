<?php

namespace App\Repository;

use App\Entity\Ceremonia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ceremonia>
 *
 * @method Ceremonia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ceremonia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ceremonia[]    findAll()
 * @method Ceremonia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CeremoniaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ceremonia::class);
    }

    public function save(Ceremonia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ceremonia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find ceremonias by category
     */
    public function findByCategoria(string $categoria): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.categoria = :categoria')
            ->setParameter('categoria', $categoria)
            ->orderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search ceremonias by name or description
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.nombre) LIKE LOWER(:query)')
            ->orWhere('LOWER(c.descripcion) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find ceremonia by name (case insensitive)
     */
    public function findByNombre(string $nombre): ?Ceremonia
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.nombre) = LOWER(:nombre)')
            ->setParameter('nombre', $nombre)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get ceremonias grouped by category
     */
    public function findGroupedByCategory(): array
    {
        $ceremonias = $this->findAll();
        $grouped = [];

        foreach ($ceremonias as $ceremonia) {
            $categoria = $ceremonia->getCategoria();
            if (!isset($grouped[$categoria])) {
                $grouped[$categoria] = [];
            }
            $grouped[$categoria][] = $ceremonia;
        }

        return $grouped;
    }

    /**
     * Get most popular ceremonias (by number of ahijados that have done them)
     */
    public function findMostPopular(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.ahijados', 'a')
            ->groupBy('c.id')
            ->orderBy('COUNT(a.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find basic ceremonies (categoría 'basica')
     */
    public function findBasicas(): array
    {
        return $this->findByCategoria('basica');
    }

    /**
     * Find advanced ceremonies (categoría 'avanzada')
     */
    public function findAvanzadas(): array
    {
        return $this->findByCategoria('avanzada');
    }
} 