<?php

namespace App\Command;

use App\Service\DatabaseService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:import-data',
    description: 'Import initial data into the database'
)]
class ImportInitialDataCommand extends Command
{
    private DatabaseService $db;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        DatabaseService $databaseService,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
        $this->db = $databaseService;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Importing Initial Data');

        try {
            // Categories
            $io->section('Creating skill categories');
            $categories = [
                ['name' => 'Programming', 'description' => 'Software development and coding skills', 'icon' => 'fa-code'],
                ['name' => 'Design', 'description' => 'Graphic, web and UX/UI design skills', 'icon' => 'fa-palette'],
                ['name' => 'Languages', 'description' => 'Foreign language learning and translation', 'icon' => 'fa-language'],
                ['name' => 'Music', 'description' => 'Musical instruments and music theory', 'icon' => 'fa-music'],
                ['name' => 'Fitness', 'description' => 'Exercise, sports and physical wellness', 'icon' => 'fa-dumbbell'],
            ];
            
            $categoryIds = [];
            foreach ($categories as $category) {
                // Check if category exists
                $existing = $this->db->fetchOne(
                    "SELECT id FROM skill_category WHERE name = :name", 
                    ['name' => $category['name']]
                );
                
                if ($existing) {
                    $io->writeln("Category {$category['name']} already exists (ID: {$existing['id']})");
                    $categoryIds[$category['name']] = $existing['id'];
                } else {
                    $category['created_at'] = date('Y-m-d H:i:s');
                    $id = $this->db->insert('skill_category', $category);
                    $io->writeln("Created category {$category['name']} (ID: $id)");
                    $categoryIds[$category['name']] = $id;
                }
            }
            
            // Skills
            $io->section('Creating skills');
            $skillsByCategory = [
                'Programming' => ['PHP', 'JavaScript', 'Python', 'Java', 'C++', 'SQL', 'HTML/CSS'],
                'Design' => ['Photoshop', 'Illustrator', 'UI Design', 'Logo Design', 'Wireframing'],
                'Languages' => ['English', 'Spanish', 'French', 'German', 'Chinese', 'Japanese'],
                'Music' => ['Guitar', 'Piano', 'Violin', 'Singing', 'Music Theory'],
                'Fitness' => ['Yoga', 'Weight Training', 'Running', 'Swimming', 'Nutrition'],
            ];
            
            $skillIds = [];
            foreach ($skillsByCategory as $category => $skills) {
                foreach ($skills as $skillName) {
                    // Check if skill exists
                    $existing = $this->db->fetchOne(
                        "SELECT id FROM skill WHERE name = :name", 
                        ['name' => $skillName]
                    );
                    
                    if ($existing) {
                        $io->writeln("Skill $skillName already exists (ID: {$existing['id']})");
                        $skillIds[$skillName] = $existing['id'];
                    } else {
                        $id = $this->db->insert('skill', ['name' => $skillName]);
                        $io->writeln("Created skill $skillName (ID: $id)");
                        $skillIds[$skillName] = $id;
                    }
                    
                    // Connect skill to category
                    $categoryId = $categoryIds[$category];
                    
                    // Check if relationship exists
                    $existingRel = $this->db->fetchOne(
                        "SELECT id FROM skill_category_skill WHERE category_id = :categoryId AND skill_id = :skillId",
                        ['categoryId' => $categoryId, 'skillId' => $skillIds[$skillName]]
                    );
                    
                    if (!$existingRel) {
                        $this->db->insert('skill_category_skill', [
                            'category_id' => $categoryId,
                            'skill_id' => $skillIds[$skillName],
                        ]);
                    }
                }
            }
            
            // Users
            $io->section('Creating users');
            $users = [
                [
                    'username' => 'admin',
                    'email' => 'admin@example.com',
                    'password' => 'admin123',
                    'roles' => json_encode(['ROLE_ADMIN', 'ROLE_USER']),
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                ],
                [
                    'username' => 'john',
                    'email' => 'john@example.com',
                    'password' => 'password123',
                    'roles' => json_encode(['ROLE_USER']),
                    'first_name' => 'John',
                    'last_name' => 'Smith',
                ],
                [
                    'username' => 'jane',
                    'email' => 'jane@example.com',
                    'password' => 'password123',
                    'roles' => json_encode(['ROLE_USER']),
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                ],
            ];
            
            $userIds = [];
            foreach ($users as $userData) {
                // Check if user exists
                $existing = $this->db->fetchOne(
                    "SELECT id FROM user WHERE username = :username OR email = :email", 
                    ['username' => $userData['username'], 'email' => $userData['email']]
                );
                
                if ($existing) {
                    $io->writeln("User {$userData['username']} already exists (ID: {$existing['id']})");
                    $userIds[$userData['username']] = $existing['id'];
                } else {
                    // Hash password
                    $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
                    $userData['password'] = $hashedPassword;
                    
                    // Add created date
                    $userData['created_at'] = date('Y-m-d H:i:s');
                    
                    $id = $this->db->insert('user', $userData);
                    $io->writeln("Created user {$userData['username']} (ID: $id)");
                    $userIds[$userData['username']] = $id;
                }
            }
            
            $io->success('Data import completed successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error importing data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 