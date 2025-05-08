<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Skill;
use App\Entity\UserSkillOffered;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSkillOffered>
 *
 * @method UserSkillOffered|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSkillOffered|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSkillOffered[]    findAll()
 * @method UserSkillOffered[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSkillOfferedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSkillOffered::class);
    }

    public function save(UserSkillOffered $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserSkillOffered $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUserAndSkill(User $user, Skill $skill): ?UserSkillOffered
    {
        return $this->findOneBy([
            'user' => $user,
            'skill' => $skill
        ]);
    }
} 