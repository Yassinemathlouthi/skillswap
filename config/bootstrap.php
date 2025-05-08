<?php

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->usePutenv();

// Load config.env directly - main source of environment variables
if (is_file(dirname(__DIR__).'/config.env')) {
    $dotenv->load(dirname(__DIR__).'/config.env');
    // Debug info to help troubleshoot
    // Uncomment if you need to debug environment loading
    // error_log('config.env loaded successfully with APP_SECRET: ' . (getenv('APP_SECRET') ? 'set' : 'not set'));
} else {
    throw new \RuntimeException('config.env file is required but not found. Please create it at the project root.');
}