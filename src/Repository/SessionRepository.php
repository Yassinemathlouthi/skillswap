<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 *
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * Find all sessions for a user (both as fromUser and toUser)
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.fromUser = :user OR s.toUser = :user')
            ->setParameter('user', $user)
            ->orderBy('s.dateTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all upcoming sessions for a user
     */
    public function findUpcomingSessionsByUser(User $user): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('s')
            ->where('s.fromUser = :user OR s.toUser = :user')
            ->andWhere('s.dateTime > :now')
            ->andWhere('s.status != :canceled')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->setParameter('canceled', Session::STATUS_CANCELED)
            ->orderBy('s.dateTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all sessions between a date range
     */
    public function findSessionsBetweenDates(User $user, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.fromUser = :user OR s.toUser = :user')
            ->andWhere('s.dateTime >= :startDate')
            ->andWhere('s.dateTime <= :endDate')
            ->andWhere('s.status != :canceled')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('canceled', Session::STATUS_CANCELED)
            ->orderBy('s.dateTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find sessions needing reminder notification
     */
    public function findSessionsNeedingReminder(\DateTime $now): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.status = :confirmed')
            ->andWhere('s.reminderSent = :notSent OR s.reminderSent IS NULL')
            ->andWhere('DATE_SUB(s.dateTime, s.reminderMinutesBefore, \'MINUTE\') <= :now')
            ->andWhere('s.dateTime > :now')
            ->setParameter('confirmed', Session::STATUS_CONFIRMED)
            ->setParameter('notSent', false)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    public function save(Session $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Session $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
} 