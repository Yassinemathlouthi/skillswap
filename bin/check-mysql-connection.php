<?php

echo "Checking MySQL database connection...\n\n";

$host = '127.0.0.1';
$port = 3306;
$dbname = 'skillswap';
$user = 'root';
$password = '';

try {
    // Try mysqli connection first
    echo "Testing mysqli connection:\n";
    $mysqli = new mysqli($host, $user, $password, $dbname, $port);
    
    if ($mysqli->connect_error) {
        echo "✗ mysqli connection failed: " . $mysqli->connect_error . "\n";
    } else {
        echo "✓ mysqli connection successful\n";
        echo "MySQL server info: " . $mysqli->server_info . "\n";
        
        $mysqli->close();
    }
    
    // Try PDO connection
    echo "\nTesting PDO connection:\n";
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $user, $password, $options);
    echo "✓ PDO connection successful\n";
    
    // Get database tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nDatabase tables in 'skillswap':\n";
    if (empty($tables)) {
        echo "No tables found\n";
    } else {
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
}

echo "\nIf the connection was successful but Doctrine still fails, check if the Symfony app is using a different PHP configuration than the CLI.\n"; 