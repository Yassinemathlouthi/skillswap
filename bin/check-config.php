<?php

// Get the project root directory
$rootDir = dirname(__DIR__);

// Load the dotenv component from Symfony
require $rootDir . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();

// Load config.env directly
if (file_exists($rootDir . '/config.env')) {
    $dotenv->load($rootDir . '/config.env');
    echo "✓ Loaded config.env file\n";
} else {
    echo "✗ config.env file not found\n";
    exit(1);
}

// Check APP_SECRET
if (!isset($_SERVER['APP_SECRET']) || empty($_SERVER['APP_SECRET'])) {
    echo "✗ APP_SECRET is not set or empty\n";
    exit(1);
}
echo "✓ APP_SECRET is set: " . substr($_SERVER['APP_SECRET'], 0, 4) . "..." . substr($_SERVER['APP_SECRET'], -4) . "\n";

// Check DATABASE_URL 
if (!isset($_SERVER['DATABASE_URL']) || empty($_SERVER['DATABASE_URL'])) {
    echo "✗ DATABASE_URL is not set or empty\n";
    exit(1);
}
echo "✓ DATABASE_URL is set and points to: " . preg_replace('/mysql:\/\/.*?@/', 'mysql://[user]@', $_SERVER['DATABASE_URL']) . "\n";

// Additional environment checks
echo "\nEnvironment Settings:\n";
echo "APP_ENV: " . ($_SERVER['APP_ENV'] ?? 'not set') . "\n";
echo "APP_DEBUG: " . (isset($_SERVER['APP_DEBUG']) ? ($_SERVER['APP_DEBUG'] ? 'true' : 'false') : 'not set') . "\n";

echo "\nAll configuration appears to be valid. If you're still experiencing issues, try:\n";
echo "1. php bin/console cache:clear\n";
echo "2. Restart your Symfony server\n"; 