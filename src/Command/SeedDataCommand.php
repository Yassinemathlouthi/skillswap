<?php

namespace App\Command;

use App\Service\DataSeederService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-data',
    description: 'Seed the database with sample data',
)]
class SeedDataCommand extends Command
{
    private DataSeederService $seederService;

    public function __construct(DataSeederService $seederService)
    {
        parent::__construct();
        $this->seederService = $seederService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Seeding database with sample data');
        
        try {
            $io->section('Seeding users...');
            $this->seederService->seedUsers();
            $io->success('Users created successfully');
            
            $io->section('Seeding skill categories...');
            $this->seederService->seedSkillCategories();
            $io->success('Skill categories created successfully');
            
            $io->section('Seeding sessions...');
            $this->seederService->seedSessions();
            $io->success('Sessions created successfully');
            
            $io->section('Seeding messages...');
            $this->seederService->seedMessages();
            $io->success('Messages created successfully');
            
            $io->section('Seeding reviews...');
            $this->seederService->seedReviews();
            $io->success('Reviews created successfully');
            
            $io->success('All data has been seeded successfully!');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred while seeding data: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
} 