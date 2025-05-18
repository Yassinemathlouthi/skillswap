<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Skill;
use App\Entity\UserSkillOffered;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:add-test-users',
    description: 'Add test users in different cities with PHP and HTML/CSS skills',
)]
class AddTestUsersCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SkillRepository $skillRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        SkillRepository $skillRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->skillRepository = $skillRepository;
        $this->passwordHasher = $passwordHasher;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Find PHP and HTML/CSS skills
        $phpSkill = $this->skillRepository->findOneBy(['name' => 'PHP']);
        $htmlCssSkill = $this->skillRepository->findOneBy(['name' => 'HTML/CSS']);
        
        if (!$phpSkill || !$htmlCssSkill) {
            $io->error('Required skills not found. Please make sure PHP and HTML/CSS skills exist in the database.');
            return Command::FAILURE;
        }
        
        // City data with coordinates
        $cities = [
            [
                'name' => 'Gabes',
                'latitude' => 33.8815, 
                'longitude' => 10.0982,
                'users' => [
                    ['username' => 'gabes_dev1', 'email' => 'gabes1@gmail.com', 'firstName' => 'Ahmed', 'lastName' => 'Ben Ali'],
                    ['username' => 'gabes_dev2', 'email' => 'gabes2@gmail.com', 'firstName' => 'Sarra', 'lastName' => 'Trabelsi'],
                    ['username' => 'gabes_dev3', 'email' => 'gabes3@gmail.com', 'firstName' => 'Karim', 'lastName' => 'Mejri'],
                ]
            ],
            [
                'name' => 'Sousse',
                'latitude' => 35.8245, 
                'longitude' => 10.6346,
                'users' => [
                    ['username' => 'sousse_dev1', 'email' => 'sousse1@gmail.com', 'firstName' => 'Mohamed', 'lastName' => 'Sassi'],
                    ['username' => 'sousse_dev2', 'email' => 'sousse2@gmail.com', 'firstName' => 'Leila', 'lastName' => 'Chaari'],
                    ['username' => 'sousse_dev3', 'email' => 'sousse3@gmail.com', 'firstName' => 'Youssef', 'lastName' => 'Hamdi'],
                ]
            ]
        ];
        
        $usersCreated = 0;
        
        foreach ($cities as $city) {
            $io->section('Adding users in ' . $city['name']);
            
            foreach ($city['users'] as $userData) {
                // Check if user already exists
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);
                
                if ($existingUser) {
                    $io->text("User {$userData['username']} already exists. Skipping...");
                    continue;
                }
                
                // Create new user
                $user = new User();
                $user->setUsername($userData['username']);
                $user->setEmail($userData['email']);
                $user->setFirstName($userData['firstName']);
                $user->setLastName($userData['lastName']);
                $user->setRoles(['ROLE_USER']);
                $user->setLocation($city['name'] . ', Tunisia');
                $user->setLatitude($city['latitude']);
                $user->setLongitude($city['longitude']);
                
                // Set password
                $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
                $user->setPassword($hashedPassword);
                
                // Add a profile description
                $user->setBio("Web developer based in {$city['name']} with expertise in PHP and front-end development.");
                
                // Save the user
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                // Add skills
                $this->addSkillToUser($user, $phpSkill);
                $this->addSkillToUser($user, $htmlCssSkill);
                
                $io->text("Created user: {$userData['username']} in {$city['name']}");
                $usersCreated++;
            }
        }
        
        $io->success("Successfully created $usersCreated new test users in Gabes and Sousse.");
        
        return Command::SUCCESS;
    }
    
    private function addSkillToUser(User $user, Skill $skill): void
    {
        $userSkill = new UserSkillOffered();
        $userSkill->setUser($user);
        $userSkill->setSkill($skill);
        
        $this->entityManager->persist($userSkill);
        $this->entityManager->flush();
    }
} 