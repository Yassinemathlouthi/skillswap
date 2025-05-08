<?php

// Get the project root directory
$rootDir = dirname(__DIR__);

// Check if config.env exists
$configEnvPath = $rootDir . '/config.env';
if (file_exists($configEnvPath)) {
    // Read the contents
    $configEnvContent = file_get_contents($configEnvPath);
    
    // Create .env file with the same content
    file_put_contents($rootDir . '/.env', $configEnvContent);
    
    echo "Successfully copied config.env to .env\n";
} else {
    echo "config.env file not found\n";
}

// Make sure APP_SECRET is properly set
$envPath = $rootDir . '/.env';
if (file_exists($envPath)) {
    $content = file_get_contents($envPath);
    
    // Check if APP_SECRET is empty or missing
    if (!preg_match('/APP_SECRET\s*=\s*[a-zA-Z0-9]+/', $content)) {
        // Generate a new APP_SECRET
        $newSecret = bin2hex(random_bytes(16));
        
        // If APP_SECRET line exists but is empty, replace it
        if (preg_match('/APP_SECRET\s*=\s*/', $content)) {
            $content = preg_replace('/APP_SECRET\s*=\s*/', "APP_SECRET={$newSecret}", $content);
        } else {
            // Otherwise add it
            $content .= "\nAPP_SECRET={$newSecret}\n";
        }
        
        file_put_contents($envPath, $content);
        echo "APP_SECRET has been set\n";
    } else {
        echo "APP_SECRET is already properly configured\n";
    }
} else {
    echo ".env file not found\n";
} 