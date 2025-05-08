<?php

namespace App\Repository;

use App\Entity\SkillCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SkillCategory>
 *
 * @method SkillCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method SkillCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method SkillCategory[]    findAll()
 * @method SkillCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SkillCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SkillCategory::class);
    }

    public function save(SkillCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SkillCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByName(string $name): ?SkillCategory
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findOrCreate(string $name, ?string $description = null, ?string $icon = null): SkillCategory
    {
        $category = $this->findByName($name);
        
        if (!$category) {
            $category = new SkillCategory();
            $category->setName($name);
            
            if ($description) {
                $category->setDescription($description);
            }
            
            if ($icon) {
                $category->setIcon($icon);
            }
            
            $this->save($category, true);
        }
        
        return $category;
    }
} 