<?php

// Get the project root directory
$rootDir = dirname(__DIR__);

// Check if APP_SECRET is set in all relevant places
$configEnvPath = $rootDir . '/config.env';
$envPath = $rootDir . '/.env';
$envLocalPath = $rootDir . '/.env.local';

// Generate a new strong APP_SECRET
$newSecret = bin2hex(random_bytes(16));

// Check config.env
if (file_exists($configEnvPath)) {
    $content = file_get_contents($configEnvPath);
    if (!preg_match('/APP_SECRET\s*=\s*[a-zA-Z0-9]+/', $content)) {
        // No valid APP_SECRET found
        if (preg_match('/APP_SECRET\s*=/', $content)) {
            // Replace empty APP_SECRET
            $content = preg_replace('/APP_SECRET\s*=\s*.*/', "APP_SECRET={$newSecret}", $content);
        } else {
            // Add APP_SECRET
            $content .= "\nAPP_SECRET={$newSecret}\n";
        }
        file_put_contents($configEnvPath, $content);
        echo "Updated APP_SECRET in config.env\n";
    } else {
        echo "APP_SECRET is already set in config.env\n";
    }
}

// Create .env.local with the APP_SECRET
$envLocalContent = "APP_SECRET={$newSecret}\n";
file_put_contents($envLocalPath, $envLocalContent);
echo "Created/updated .env.local with a new APP_SECRET\n";

echo "APP_SECRET has been fixed. Please restart your Symfony server.\n"; 