<?php

namespace App\Repository;

use App\Entity\Ahijado;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AhijadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ahijado::class);
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['created_at' => 'DESC']);
    }

    public function searchByTerm(User $user, string $term): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.name LIKE :term OR a.email LIKE :term OR a.phone LIKE :term')
            ->setParameter('user', $user)
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('a.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 