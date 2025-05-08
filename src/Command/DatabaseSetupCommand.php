<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:database:setup',
    description: 'Set up the database schema using Doctrine'
)]
class DatabaseSetupCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Set up the database schema using Doctrine');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Setting up the database schema');

        // Run the schema update command
        $io->section('Updating database schema');
        $process = new Process(['php', 'bin/console', 'doctrine:schema:update', '--force']);
        $process->run(function ($type, $buffer) use ($io) {
            $io->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $io->error('Failed to update database schema. Please check your database connection and try again.');
            return Command::FAILURE;
        }

        $io->success('Database schema has been updated successfully.');
        return Command::SUCCESS;
    }
} 