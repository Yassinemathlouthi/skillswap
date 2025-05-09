<?php

namespace App\Command;

use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Entity\SkillCategorySkill;
use App\Repository\SkillCategoryRepository;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ensure-skills',
    description: 'Ensures that required skills and categories exist in the database',
)]
class EnsureSkillsExistCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SkillCategoryRepository $categoryRepository;
    private SkillRepository $skillRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SkillCategoryRepository $categoryRepository,
        SkillRepository $skillRepository
    ) {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
        $this->skillRepository = $skillRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Ensuring required skills and categories exist');
        
        // Define required categories with descriptions and optional icons
        $requiredCategories = [
            'Programming' => [
                'description' => 'General programming languages and concepts',
                'icon' => 'fas fa-code'
            ],
            'Web Development' => [
                'description' => 'Building and maintaining websites',
                'icon' => 'fas fa-globe'
            ],
            'Backend Development' => [
                'description' => 'Server-side application logic and database management',
                'icon' => 'fas fa-server'
            ],
            'Frontend Development' => [
                'description' => 'User interface and client-side development',
                'icon' => 'fas fa-laptop-code'
            ],
            'Mobile Development' => [
                'description' => 'Building applications for mobile devices',
                'icon' => 'fas fa-mobile-alt'
            ],
            'Data Science' => [
                'description' => 'Working with data, statistics, and machine learning',
                'icon' => 'fas fa-chart-bar'
            ],
            'Music' => [
                'description' => 'Musical instruments and music theory',
                'icon' => 'fas fa-music'
            ],
            'Fitness' => [
                'description' => 'Exercise, sports and physical wellness',
                'icon' => 'fas fa-dumbbell'
            ],
            'Languages' => [
                'description' => 'Foreign languages and communication skills',
                'icon' => 'fas fa-language'
            ],
            'DevOps' => [
                'description' => 'Development operations, deployment and infrastructure',
                'icon' => 'fas fa-cogs'
            ]
        ];
        
        // Required skills with their categories
        $requiredSkills = [
            // Web Development Skills
            'PHP' => ['Programming', 'Backend Development', 'Web Development'],
            'HTML/CSS' => ['Web Development', 'Frontend Development'],
            'JavaScript' => ['Programming', 'Web Development', 'Frontend Development'],
            'Python' => ['Programming', 'Backend Development', 'Data Science'],
            'Java' => ['Programming', 'Backend Development', 'Mobile Development'],
            
            // Music Skills
            'Guitar' => ['Music'],
            'Piano' => ['Music'],
            'Violin' => ['Music'],
            'Singing' => ['Music'],
            'Music Theory' => ['Music'],
            
            // Fitness Skills
            'Yoga' => ['Fitness'],
            'Weight Training' => ['Fitness'],
            'Running' => ['Fitness'],
            'Swimming' => ['Fitness'],
            'Nutrition' => ['Fitness'],
            
            // Languages
            'English' => ['Languages'],
            
            // Additional skills from the various categories
            'React' => ['Frontend Development', 'Web Development'],
            'Vue.js' => ['Frontend Development', 'Web Development'],
            'Angular' => ['Frontend Development', 'Web Development'],
            'Node.js' => ['Backend Development', 'Web Development'],
            'SQL' => ['Backend Development', 'Data Science'],
            'Docker' => ['Backend Development', 'DevOps'],
            'Machine Learning' => ['Data Science'],
            'Data Analysis' => ['Data Science'],
            'iOS Development' => ['Mobile Development'],
            'Android Development' => ['Mobile Development'],
            'React Native' => ['Mobile Development', 'Frontend Development'],
            'Flutter' => ['Mobile Development']
        ];
        
        // Ensure categories exist
        $categories = [];
        $categoriesCreated = 0;
        
        foreach ($requiredCategories as $categoryName => $categoryData) {
            $category = $this->categoryRepository->findOneBy(['name' => $categoryName]);
            
            if (!$category) {
                $io->text("Creating category: $categoryName");
                $category = new SkillCategory();
                $category->setName($categoryName);
                $category->setDescription($categoryData['description'] ?? null);
                $category->setIcon($categoryData['icon'] ?? null);
                $this->entityManager->persist($category);
                $categoriesCreated++;
            } else {
                // Update description and icon if provided
                if (isset($categoryData['description']) && empty($category->getDescription())) {
                    $category->setDescription($categoryData['description']);
                }
                if (isset($categoryData['icon']) && empty($category->getIcon())) {
                    $category->setIcon($categoryData['icon']);
                }
            }
            
            $categories[$categoryName] = $category;
        }
        
        if ($categoriesCreated > 0) {
            $this->entityManager->flush();
            $io->success("Created $categoriesCreated new categories.");
        }
        
        // Ensure skills exist
        $skillsCreated = 0;
        $relationshipsCreated = 0;
        
        foreach ($requiredSkills as $skillName => $skillCategories) {
            $skill = $this->skillRepository->findOneBy(['name' => $skillName]);
            
            if (!$skill) {
                $io->text("Creating skill: $skillName");
                $skill = new Skill();
                $skill->setName($skillName);
                $this->entityManager->persist($skill);
                $skillsCreated++;
            }
            
            // Add skill categories
            foreach ($skillCategories as $categoryName) {
                if (!isset($categories[$categoryName])) {
                    $io->warning("Category '$categoryName' not found for skill '$skillName'");
                    continue;
                }
                
                $category = $categories[$categoryName];
                
                // Check if this relationship already exists
                $hasRelationship = false;
                foreach ($skill->getSkillCategories() as $existingRelation) {
                    if ($existingRelation->getCategory() === $category) {
                        $hasRelationship = true;
                        break;
                    }
                }
                
                if (!$hasRelationship) {
                    $skillCategorySkill = new SkillCategorySkill();
                    $skillCategorySkill->setSkill($skill);
                    $skillCategorySkill->setCategory($category);
                    $this->entityManager->persist($skillCategorySkill);
                    $relationshipsCreated++;
                }
            }
        }
        
        $this->entityManager->flush();
        
        if ($skillsCreated > 0) {
            $io->success("Created $skillsCreated new skills.");
        } else {
            $io->success("All required skills already exist.");
        }
        
        if ($relationshipsCreated > 0) {
            $io->success("Created $relationshipsCreated new skill-category relationships.");
        }
        
        return Command::SUCCESS;
    }
} 