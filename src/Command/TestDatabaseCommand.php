<?php

namespace App\Command;

use App\Service\DatabaseService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-database',
    description: 'Test database connection and display table info'
)]
class TestDatabaseCommand extends Command
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct();
        $this->databaseService = $databaseService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Testing Database Connection');

        try {
            // Test connection
            $pdo = $this->databaseService->getConnection();
            $io->success('Successfully connected to the database');

            // Get server info
            $serverInfo = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $io->writeln("Server info: $serverInfo");

            // Get tables
            $tables = $this->databaseService->fetchAll("SHOW TABLES");
            
            $io->section('Database Tables');
            $tableRows = [];
            foreach ($tables as $tableData) {
                $tableName = reset($tableData);
                
                // Get row count
                $count = $this->databaseService->fetchColumn("SELECT COUNT(*) FROM `$tableName`");
                
                // Get column info
                $columns = $this->databaseService->fetchAll("SHOW COLUMNS FROM `$tableName`");
                $columnCount = count($columns);
                
                $tableRows[] = [$tableName, $columnCount, $count];
            }
            
            $io->table(
                ['Table Name', 'Columns', 'Rows'],
                $tableRows
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Database Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 