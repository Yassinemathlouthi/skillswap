<?php

namespace App\Repository;

use App\Entity\Skill;
use App\Entity\SkillCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Skill>
 *
 * @method Skill|null find($id, $lockMode = null, $lockVersion = null)
 * @method Skill|null findOneBy(array $criteria, array $orderBy = null)
 * @method Skill[]    findAll()
 * @method Skill[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    public function save(Skill $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Skill $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByName(string $name): ?Skill
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findOrCreate(string $name): Skill
    {
        $skill = $this->findByName($name);
        
        if (!$skill) {
            $skill = new Skill();
            $skill->setName($name);
            $this->save($skill, true);
        }
        
        return $skill;
    }
    
    /**
     * Find skills by search term
     */
    public function findBySearchTerm(string $searchTerm, ?int $categoryId = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.name LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('s.name', 'ASC');
        
        if ($categoryId) {
            $qb->join('s.skillCategories', 'sc')
                ->andWhere('sc.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Find skills by category
     */
    public function findByCategory(SkillCategory $category): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.skillCategories', 'sc')
            ->where('sc.category = :category')
            ->setParameter('category', $category)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 