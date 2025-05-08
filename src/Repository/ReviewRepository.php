<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 *
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function save(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByReviewedUser(User $user): array
    {
        return $this->findBy(['reviewedUser' => $user], ['createdAt' => 'DESC']);
    }

    public function findByReviewer(User $user): array
    {
        return $this->findBy(['reviewer' => $user], ['createdAt' => 'DESC']);
    }

    public function getAverageRating(User $user): float
    {
        $reviews = $this->findByReviewedUser($user);
        
        if (empty($reviews)) {
            return 0;
        }
        
        $total = 0;
        foreach ($reviews as $review) {
            $total += $review->getRating();
        }
        
        return $total / count($reviews);
    }
} 