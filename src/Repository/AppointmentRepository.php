<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findTodayByUser(User $user): array
    {
        $today = new \DateTime();
        
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.date = :today')
            ->setParameter('user', $user)
            ->setParameter('today', $today->format('Y-m-d'))
            ->orderBy('a.time', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByMonthAndUser(int $year, int $month, User $user): array
    {
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.date BETWEEN :startDate AND :endDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->orderBy('a.date', 'ASC')
            ->addOrderBy('a.time', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getMonthlyStats(int $year, int $month, User $user): array
    {
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        $qb = $this->createQueryBuilder('a')
            ->select('a.status, COUNT(a.id) as count')
            ->where('a.user = :user')
            ->andWhere('a.date BETWEEN :startDate AND :endDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->groupBy('a.status');

        $results = $qb->getQuery()->getResult();
        
        $stats = [
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
            'cancelled' => 0,
            'confirmada' => 0
        ];

        foreach ($results as $result) {
            $status = $result['status'];
            $count = (int)$result['count'];
            $stats['total'] += $count;
            
            if (isset($stats[$status])) {
                $stats[$status] = $count;
            }
        }

        return $stats;
    }

    public function findUpcomingByUser(User $user, int $days = 7): array
    {
        $today = new \DateTime();
        $endDate = clone $today;
        $endDate->modify("+{$days} days");

        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.date BETWEEN :today AND :endDate')
            ->andWhere('a.status IN (:activeStatuses)')
            ->setParameter('user', $user)
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->setParameter('activeStatuses', ['pendiente', 'confirmada'])
            ->orderBy('a.date', 'ASC')
            ->addOrderBy('a.time', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findConflictingAppointments(User $user, \DateTime $date, \DateTime $time, int $duration, ?int $excludeId = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.date = :date')
            ->andWhere('a.status IN (:activeStatuses)')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('activeStatuses', ['pendiente', 'confirmada']);

        if ($excludeId) {
            $qb->andWhere('a.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        $appointments = $qb->getQuery()->getResult();
        
        $conflicts = [];
        $requestedStart = $time;
        $requestedEnd = clone $time;
        $requestedEnd->modify("+{$duration} minutes");

        foreach ($appointments as $appointment) {
            $existingStart = $appointment->getTime();
            $existingEnd = clone $existingStart;
            $existingEnd->modify("+{$appointment->getDuration()} minutes");

            // Check for time overlap
            if (($requestedStart < $existingEnd) && ($requestedEnd > $existingStart)) {
                $conflicts[] = $appointment;
            }
        }

        return $conflicts;
    }
} 