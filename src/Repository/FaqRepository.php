<?php

namespace App\Repository;

use App\Entity\Faq;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Faq>
 *
 * @method Faq|null find($id, $lockMode = null, $lockVersion = null)
 * @method Faq|null findOneBy(array $criteria, array $orderBy = null)
 * @method Faq[]    findAll()
 * @method Faq[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    /**
     * Find all published FAQs ordered by category and display order
     */
    public function findAllPublished(): array
    {
        return $this->createQueryBuilder('f')
            // Temporarily removing the published filter
            // ->where('f.isPublished = :published')
            // ->setParameter('published', true)
            ->orderBy('f.category', 'ASC')
            // Temporarily removing this order as the column might not exist
            // ->addOrderBy('f.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all FAQs in a specific category
     */
    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.category = :category')
            // Temporarily removing the published filter
            // ->andWhere('f.isPublished = :published')
            ->setParameter('category', $category)
            // ->setParameter('published', true)
            // Temporarily removing this order as the column might not exist
            // ->orderBy('f.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all distinct FAQ categories
     */
    public function findAllCategories(): array
    {
        return $this->createQueryBuilder('f')
            ->select('DISTINCT f.category')
            // Temporarily removing the published filter
            // ->where('f.isPublished = :published')
            // ->setParameter('published', true)
            ->orderBy('f.category', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * Search FAQs by keyword in question or answer
     */
    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.question LIKE :searchTerm OR f.answer LIKE :searchTerm')
            // Temporarily removing the published filter
            // ->andWhere('f.isPublished = :published')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            // ->setParameter('published', true)
            ->orderBy('f.category', 'ASC')
            // Temporarily removing this order as the column might not exist
            // ->addOrderBy('f.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Faq $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Faq $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
} 