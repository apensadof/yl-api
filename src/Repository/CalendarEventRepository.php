<?php

namespace App\Repository;

use App\Entity\CalendarEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalendarEvent>
 *
 * @method CalendarEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method CalendarEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method CalendarEvent[]    findAll()
 * @method CalendarEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarEvent::class);
    }

    public function save(CalendarEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CalendarEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find events for a specific user, year and month
     */
    public function findByUserAndMonth(User $user, int $year, int $month, ?string $type = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('YEAR(e.date) = :year')
            ->andWhere('MONTH(e.date) = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.time', 'ASC');

        if ($type) {
            $qb->andWhere('e.type = :type')
               ->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find events for a specific user and date
     */
    public function findByUserAndDate(User $user, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.date = :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('e.time', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events by user with filtering and pagination
     */
    public function findByUserWithFilters(User $user, ?string $type = null, ?\DateTimeInterface $fromDate = null, ?\DateTimeInterface $toDate = null, int $limit = 50, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->setParameter('user', $user);

        if ($type) {
            $qb->andWhere('e.type = :type')
               ->setParameter('type', $type);
        }

        if ($fromDate) {
            $qb->andWhere('e.date >= :fromDate')
               ->setParameter('fromDate', $fromDate->format('Y-m-d'));
        }

        if ($toDate) {
            $qb->andWhere('e.date <= :toDate')
               ->setParameter('toDate', $toDate->format('Y-m-d'));
        }

        return $qb->orderBy('e.date', 'ASC')
                  ->addOrderBy('e.time', 'ASC')
                  ->setMaxResults($limit)
                  ->setFirstResult($offset)
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Get statistics for events by type for a specific user and month
     */
    public function getMonthlyStatsByType(User $user, int $year, int $month): array
    {
        $result = $this->createQueryBuilder('e')
            ->select('e.type, COUNT(e.id) as count')
            ->andWhere('e.user = :user')
            ->andWhere('YEAR(e.date) = :year')
            ->andWhere('MONTH(e.date) = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->groupBy('e.type')
            ->getQuery()
            ->getResult();

        // Convert to associative array
        $stats = [];
        foreach ($result as $row) {
            $stats[$row['type']] = (int)$row['count'];
        }

        // Ensure all event types are represented
        $allTypes = CalendarEvent::getValidEventTypes();
        foreach ($allTypes as $type) {
            if (!isset($stats[$type])) {
                $stats[$type] = 0;
            }
        }

        return $stats;
    }

    /**
     * Check for time conflicts for a user on a specific date
     */
    public function findTimeConflicts(User $user, \DateTimeInterface $date, string $time, int $duration, ?int $excludeEventId = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.date = :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y-m-d'));

        if ($excludeEventId) {
            $qb->andWhere('e.id != :excludeId')
               ->setParameter('excludeId', $excludeEventId);
        }

        $events = $qb->getQuery()->getResult();

        $conflicts = [];
        foreach ($events as $event) {
            if ($this->eventsOverlap($time, $duration, $event->getTime(), $event->getDuration())) {
                $conflicts[] = $event;
            }
        }

        return $conflicts;
    }

    /**
     * Check if two events overlap in time
     */
    private function eventsOverlap(string $time1, int $duration1, string $time2, int $duration2): bool
    {
        $start1 = new \DateTime($time1);
        $end1 = clone $start1;
        $end1->add(new \DateInterval('PT' . $duration1 . 'M'));

        $start2 = new \DateTime($time2);
        $end2 = clone $start2;
        $end2->add(new \DateInterval('PT' . $duration2 . 'M'));

        return ($start1 < $end2) && ($end1 > $start2);
    }

    /**
     * Get upcoming events for a user (next 7 days)
     */
    public function findUpcomingEvents(User $user, int $days = 7): array
    {
        $today = new \DateTime();
        $futureDate = new \DateTime();
        $futureDate->add(new \DateInterval('P' . $days . 'D'));

        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('e.date >= :today')
            ->andWhere('e.date <= :futureDate')
            ->setParameter('user', $user)
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('futureDate', $futureDate->format('Y-m-d'))
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.time', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search events by client name
     */
    public function searchByClient(User $user, string $clientName): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :user')
            ->andWhere('LOWER(e.client) LIKE LOWER(:clientName)')
            ->setParameter('user', $user)
            ->setParameter('clientName', '%' . $clientName . '%')
            ->orderBy('e.date', 'DESC')
            ->addOrderBy('e.time', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 