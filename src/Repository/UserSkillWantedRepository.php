<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Skill;
use App\Entity\UserSkillWanted;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSkillWanted>
 *
 * @method UserSkillWanted|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSkillWanted|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSkillWanted[]    findAll()
 * @method UserSkillWanted[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSkillWantedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSkillWanted::class);
    }

    public function save(UserSkillWanted $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserSkillWanted $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUserAndSkill(User $user, Skill $skill): ?UserSkillWanted
    {
        return $this->findOneBy([
            'user' => $user,
            'skill' => $skill
        ]);
    }
} 