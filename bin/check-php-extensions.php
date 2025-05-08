<?php

echo "PHP Version: " . PHP_VERSION . "\n\n";
echo "Checking required extensions:\n";

$requiredExtensions = [
    'pdo',
    'pdo_mysql',
    'mysqli',
    'json',
    'xml',
    'mbstring',
    'intl'
];

foreach ($requiredExtensions as $extension) {
    if (extension_loaded($extension)) {
        echo "✓ {$extension} extension is loaded\n";
    } else {
        echo "✗ {$extension} extension is NOT loaded\n";
    }
}

echo "\nLoaded PDO drivers:\n";
if (extension_loaded('pdo')) {
    $drivers = \PDO::getAvailableDrivers();
    if (empty($drivers)) {
        echo "No PDO drivers available\n";
    } else {
        foreach ($drivers as $driver) {
            echo "- {$driver}\n";
        }
    }
} else {
    echo "PDO extension is not loaded\n";
}

echo "\nPHP INI location: " . php_ini_loaded_file() . "\n";

echo "\nInstructions to fix missing extensions:\n";
echo "1. Open php.ini file\n";
echo "2. Uncomment the following lines by removing the semicolon (;) at the beginning:\n";
echo "   extension=pdo_mysql\n";
echo "   extension=mysqli\n";
echo "3. Save the file and restart your web server\n"; 