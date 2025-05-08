<?php

namespace App\Repository;

use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Entity\SkillCategorySkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SkillCategorySkill>
 *
 * @method SkillCategorySkill|null find($id, $lockMode = null, $lockVersion = null)
 * @method SkillCategorySkill|null findOneBy(array $criteria, array $orderBy = null)
 * @method SkillCategorySkill[]    findAll()
 * @method SkillCategorySkill[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SkillCategorySkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SkillCategorySkill::class);
    }

    public function save(SkillCategorySkill $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SkillCategorySkill $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByCategoryAndSkill(SkillCategory $category, Skill $skill): ?SkillCategorySkill
    {
        return $this->findOneBy([
            'category' => $category,
            'skill' => $skill
        ]);
    }
} 