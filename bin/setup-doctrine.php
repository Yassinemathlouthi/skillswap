<?php

// Get the project root directory
$rootDir = dirname(__DIR__);

// Check if Doctrine is already installed
$doctrineConfigPath = $rootDir . '/config/packages/doctrine.yaml';
if (!file_exists($doctrineConfigPath)) {
    echo "Doctrine configuration not found. Please install doctrine-bundle:\n";
    echo "composer require symfony/orm-pack\n";
    exit(1);
}

// Update .env file with DATABASE_URL
$envPath = $rootDir . '/.env';
if (file_exists($envPath)) {
    $content = file_get_contents($envPath);
    
    // Check if DATABASE_URL already exists
    if (!preg_match('/DATABASE_URL\s*=/', $content)) {
        // Add DATABASE_URL
        $content .= "\n# Database configuration for MySQL\n";
        $content .= 'DATABASE_URL="mysql://root:@127.0.0.1:3306/skillswap?serverVersion=8.0.32&charset=utf8mb4"' . "\n";
        
        file_put_contents($envPath, $content);
        echo "DATABASE_URL has been added to .env\n";
    } else {
        echo "DATABASE_URL is already configured in .env\n";
    }
} else {
    echo ".env file not found\n";
    exit(1);
}

// Check Doctrine configuration
$doctrineConfig = file_get_contents($doctrineConfigPath);
if (strpos($doctrineConfig, 'dbal:') === false) {
    echo "Doctrine configuration seems incomplete. Please check doctrine.yaml\n";
    exit(1);
}

echo "Doctrine ORM is properly configured for MySQL.\n";
echo "Run 'composer require symfony/orm-pack' if you haven't already installed Doctrine ORM.\n";
echo "Then run 'php bin/console doctrine:schema:update --force' to update your database schema.\n"; 