<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserMatchingService
{
    private UserRepository $userRepository;
    private SkillRepository $skillRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        SkillRepository $skillRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->skillRepository = $skillRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Find users that can teach skills that the current user wants to learn
     */
    public function findTeachersForUser(User $user, int $limit = 5): array
    {
        // Get skills the user wants to learn
        $wantedSkills = $user->getSkillsWanted();
        if ($wantedSkills->isEmpty()) {
            return [];
        }

        // Extract skill IDs
        $skillIds = [];
        foreach ($wantedSkills as $userSkill) {
            $skillIds[] = $userSkill->getSkill()->getId();
        }

        if (empty($skillIds)) {
            return [];
        }

        // Find users who can teach these skills
        $conn = $this->entityManager->getConnection();
        
        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($skillIds), '?'));
        
        $sql = '
            SELECT u.*, 
                COUNT(DISTINCT so.skill_id) as matching_skills,
                GROUP_CONCAT(DISTINCT s.name SEPARATOR \',\') as skill_names
            FROM user u
            JOIN user_skill_offered so ON u.id = so.user_id
            JOIN skill s ON so.skill_id = s.id
            WHERE so.skill_id IN (' . $placeholders . ')
            AND u.id != ?
            GROUP BY u.id
            ORDER BY COUNT(DISTINCT so.skill_id) DESC, u.username ASC
            LIMIT ' . $limit . '
        ';
        
        // Create parameters array with positional placeholders
        $params = array_merge($skillIds, [$user->getId()]);
        
        // Execute the query
        $resultSet = $conn->executeQuery($sql, $params)->fetchAllAssociative();
        
        // Format the results into a more useful structure
        $matches = [];
        foreach ($resultSet as $row) {
            $teacherUser = $this->userRepository->find($row['id']);
            if (!$teacherUser) {
                continue;
            }
            
            $skillNames = explode(',', $row['skill_names'] ?? '');
            $matchingSkills = [];
            
            foreach ($skillNames as $skillName) {
                if (empty(trim($skillName))) {
                    continue;
                }
                foreach ($wantedSkills as $userSkill) {
                    if ($userSkill->getSkill()->getName() === $skillName) {
                        $matchingSkills[] = $userSkill->getSkill();
                        break;
                    }
                }
            }
            
            $matches[] = [
                'user' => $teacherUser,
                'matchCount' => (int)$row['matching_skills'],
                'matchingSkills' => $matchingSkills
            ];
        }
        
        return $matches;
    }

    /**
     * Find users that want to learn skills that the current user can teach
     */
    public function findStudentsForUser(User $user, int $limit = 5): array
    {
        // Get skills the user can teach
        $offeredSkills = $user->getSkillsOffered();
        if ($offeredSkills->isEmpty()) {
            return [];
        }

        // Extract skill IDs
        $skillIds = [];
        foreach ($offeredSkills as $userSkill) {
            $skillIds[] = $userSkill->getSkill()->getId();
        }

        if (empty($skillIds)) {
            return [];
        }

        // Find users who want to learn these skills
        $conn = $this->entityManager->getConnection();
        
        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($skillIds), '?'));
        
        $sql = '
            SELECT u.*, 
                COUNT(DISTINCT sw.skill_id) as matching_skills,
                GROUP_CONCAT(DISTINCT s.name SEPARATOR \',\') as skill_names
            FROM user u
            JOIN user_skill_wanted sw ON u.id = sw.user_id
            JOIN skill s ON sw.skill_id = s.id
            WHERE sw.skill_id IN (' . $placeholders . ')
            AND u.id != ?
            GROUP BY u.id
            ORDER BY COUNT(DISTINCT sw.skill_id) DESC, u.username ASC
            LIMIT ' . $limit . '
        ';
        
        // Create parameters array with positional placeholders
        $params = array_merge($skillIds, [$user->getId()]);
        
        // Execute the query
        $resultSet = $conn->executeQuery($sql, $params)->fetchAllAssociative();
        
        // Format the results into a more useful structure
        $matches = [];
        foreach ($resultSet as $row) {
            $studentUser = $this->userRepository->find($row['id']);
            if (!$studentUser) {
                continue;
            }
            
            $skillNames = explode(',', $row['skill_names'] ?? '');
            $matchingSkills = [];
            
            foreach ($skillNames as $skillName) {
                if (empty(trim($skillName))) {
                    continue;
                }
                foreach ($offeredSkills as $userSkill) {
                    if ($userSkill->getSkill()->getName() === $skillName) {
                        $matchingSkills[] = $userSkill->getSkill();
                        break;
                    }
                }
            }
            
            $matches[] = [
                'user' => $studentUser,
                'matchCount' => (int)$row['matching_skills'],
                'matchingSkills' => $matchingSkills
            ];
        }
        
        return $matches;
    }

    /**
     * Find perfect matches where users can teach each other different skills
     */
    public function findPerfectMatches(User $user, int $limit = 5): array
    {
        // Get skills the user can teach and wants to learn
        $offeredSkills = $user->getSkillsOffered();
        $wantedSkills = $user->getSkillsWanted();
        
        if ($offeredSkills->isEmpty() || $wantedSkills->isEmpty()) {
            return [];
        }

        // Extract skill IDs
        $offeredSkillIds = [];
        foreach ($offeredSkills as $userSkill) {
            $offeredSkillIds[] = $userSkill->getSkill()->getId();
        }
        
        $wantedSkillIds = [];
        foreach ($wantedSkills as $userSkill) {
            $wantedSkillIds[] = $userSkill->getSkill()->getId();
        }

        if (empty($offeredSkillIds) || empty($wantedSkillIds)) {
            return [];
        }

        // Find users who:
        // 1. Want to learn skills the current user offers
        // 2. Can teach skills the current user wants to learn
        $conn = $this->entityManager->getConnection();
        
        // Create placeholders for the IN clauses
        $offeredPlaceholders = implode(',', array_fill(0, count($offeredSkillIds), '?'));
        $wantedPlaceholders = implode(',', array_fill(0, count($wantedSkillIds), '?'));
        
        $sql = '
            SELECT u.*, 
                COUNT(DISTINCT swo.skill_id) as you_can_teach_count,
                COUNT(DISTINCT oso.skill_id) as they_can_teach_count,
                GROUP_CONCAT(DISTINCT s1.name SEPARATOR \',\') as you_teach_skills,
                GROUP_CONCAT(DISTINCT s2.name SEPARATOR \',\') as they_teach_skills,
                COUNT(DISTINCT swo.skill_id) + COUNT(DISTINCT oso.skill_id) as total_count
            FROM user u
            -- Skills they want that you offer
            JOIN user_skill_wanted swo ON u.id = swo.user_id
            JOIN skill s1 ON swo.skill_id = s1.id AND swo.skill_id IN (' . $offeredPlaceholders . ')
            -- Skills they offer that you want
            JOIN user_skill_offered oso ON u.id = oso.user_id
            JOIN skill s2 ON oso.skill_id = s2.id AND oso.skill_id IN (' . $wantedPlaceholders . ')
            WHERE u.id != ?
            GROUP BY u.id
            ORDER BY total_count DESC, u.username ASC
            LIMIT ' . $limit . '
        ';
        
        // Create parameters array with positional placeholders
        $params = array_merge($offeredSkillIds, $wantedSkillIds, [$user->getId()]);
        
        // Execute the query
        $resultSet = $conn->executeQuery($sql, $params)->fetchAllAssociative();
        
        // Format the results into a more useful structure
        $matches = [];
        foreach ($resultSet as $row) {
            $matchedUser = $this->userRepository->find($row['id']);
            if (!$matchedUser) {
                continue;
            }
            
            $youTeachSkillNames = explode(',', $row['you_teach_skills'] ?? '');
            $theyTeachSkillNames = explode(',', $row['they_teach_skills'] ?? '');
            
            $youTeachSkills = [];
            foreach ($youTeachSkillNames as $skillName) {
                if (empty(trim($skillName))) {
                    continue;
                }
                foreach ($offeredSkills as $userSkill) {
                    if ($userSkill->getSkill()->getName() === $skillName) {
                        $youTeachSkills[] = $userSkill->getSkill();
                        break;
                    }
                }
            }
            
            $theyTeachSkills = [];
            foreach ($theyTeachSkillNames as $skillName) {
                if (empty(trim($skillName))) {
                    continue;
                }
                foreach ($wantedSkills as $userSkill) {
                    if ($userSkill->getSkill()->getName() === $skillName) {
                        $theyTeachSkills[] = $userSkill->getSkill();
                        break;
                    }
                }
            }
            
            $matches[] = [
                'user' => $matchedUser,
                'youTeachCount' => (int)$row['you_can_teach_count'],
                'theyTeachCount' => (int)$row['they_can_teach_count'],
                'youTeachSkills' => $youTeachSkills,
                'theyTeachSkills' => $theyTeachSkills,
                'totalMatchScore' => (int)$row['total_count']
            ];
        }
        
        return $matches;
    }
} 