<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserAvailability>
 *
 * @method UserAvailability|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAvailability|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAvailability[]    findAll()
 * @method UserAvailability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAvailability::class);
    }

    public function save(UserAvailability $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserAvailability $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user],
            ['dayOfWeek' => 'ASC', 'startTime' => 'ASC']
        );
    }
} 