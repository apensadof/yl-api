<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function findValidTokenByUser(User $user): ?PasswordResetToken
    {
        return $this->createQueryBuilder('prt')
            ->where('prt.user = :user')
            ->andWhere('prt.used = false')
            ->andWhere('prt.expires_at > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->orderBy('prt.created_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findValidTokenByTokenString(string $token): ?PasswordResetToken
    {
        return $this->createQueryBuilder('prt')
            ->where('prt.token = :token')
            ->andWhere('prt.used = false')
            ->andWhere('prt.expires_at > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function invalidateUserTokens(User $user): void
    {
        $this->createQueryBuilder('prt')
            ->update()
            ->set('prt.used', true)
            ->where('prt.user = :user')
            ->andWhere('prt.used = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function cleanExpiredTokens(): int
    {
        return $this->createQueryBuilder('prt')
            ->delete()
            ->where('prt.expires_at < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }
} 