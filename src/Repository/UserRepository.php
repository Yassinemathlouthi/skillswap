<?php

namespace App\Repository;

use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findBySkillOffered(string $skill): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.skillsOffered', 'so')
            ->join('so.skill', 's')
            ->where('s.name = :skill')
            ->setParameter('skill', $skill)
            ->getQuery()
            ->getResult();
    }

    public function findBySkillWanted(string $skill): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.skillsWanted', 'sw')
            ->join('sw.skill', 's')
            ->where('s.name = :skill')
            ->setParameter('skill', $skill)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Find users who have the specified skills (either offered or wanted)
     */
    public function findBySkills(array $skills): array
    {
        if (empty($skills)) {
            return [];
        }
        
        $skillIds = array_map(function($skill) {
            return $skill->getId();
        }, $skills);
        
        $qb = $this->createQueryBuilder('u');
        
        return $qb->leftJoin('u.skillsOffered', 'so')
            ->leftJoin('so.skill', 'soskill')
            ->leftJoin('u.skillsWanted', 'sw')
            ->leftJoin('sw.skill', 'swskill')
            ->where($qb->expr()->orX(
                $qb->expr()->in('soskill.id', ':skillIds'),
                $qb->expr()->in('swskill.id', ':skillIds')
            ))
            ->setParameter('skillIds', $skillIds)
            ->groupBy('u.id')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Find users who have skills in the specified category (either offered or wanted)
     */
    public function findBySkillsInCategory(SkillCategory $category): array
    {
        $qb = $this->createQueryBuilder('u');
        
        return $qb->leftJoin('u.skillsOffered', 'so')
            ->leftJoin('so.skill', 'soskill')
            ->leftJoin('soskill.skillCategories', 'sosc')
            ->leftJoin('u.skillsWanted', 'sw')
            ->leftJoin('sw.skill', 'swskill')
            ->leftJoin('swskill.skillCategories', 'swsc')
            ->where($qb->expr()->orX(
                $qb->expr()->eq('sosc.category', ':category'),
                $qb->expr()->eq('swsc.category', ':category')
            ))
            ->setParameter('category', $category)
            ->groupBy('u.id')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->save($user, true);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }
} 