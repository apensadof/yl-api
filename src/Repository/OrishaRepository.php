<?php

namespace App\Repository;

use App\Entity\Orisha;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orisha>
 *
 * @method Orisha|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orisha|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orisha[]    findAll()
 * @method Orisha[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrishaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orisha::class);
    }

    public function save(Orisha $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Orisha $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find orishas by category
     */
    public function findByCategoria(string $categoria): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.categoria = :categoria')
            ->setParameter('categoria', $categoria)
            ->orderBy('o.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search orishas by name or attributes
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('o')
            ->where('LOWER(o.nombre) LIKE LOWER(:query)')
            ->orWhere('LOWER(o.dominio) LIKE LOWER(:query)')
            ->orWhere('LOWER(o.sincretismo) LIKE LOWER(:query)')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('o.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find orisha by name (case insensitive)
     */
    public function findByNombre(string $nombre): ?Orisha
    {
        return $this->createQueryBuilder('o')
            ->where('LOWER(o.nombre) = LOWER(:nombre)')
            ->setParameter('nombre', $nombre)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get orishas grouped by category
     */
    public function findGroupedByCategory(): array
    {
        $orishas = $this->findAll();
        $grouped = [];

        foreach ($orishas as $orisha) {
            $categoria = $orisha->getCategoria() ?? 'otros';
            if (!isset($grouped[$categoria])) {
                $grouped[$categoria] = [];
            }
            $grouped[$categoria][] = $orisha;
        }

        return $grouped;
    }

    /**
     * Get most popular orishas (by number of ahijados that have them)
     */
    public function findMostPopular(int $limit = 10): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.ahijadosQueLoRecibieron', 'a')
            ->groupBy('o.id')
            ->orderBy('COUNT(a.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
} 