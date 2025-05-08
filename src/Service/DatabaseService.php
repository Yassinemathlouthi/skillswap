<?php

namespace App\Service;

use PDO;
use PDOException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service for direct database access when Doctrine is not working
 */
class DatabaseService
{
    private ?PDO $connection = null;
    private string $host;
    private string $dbname;
    private string $user;
    private string $password;
    private int $port;
    
    public function __construct(ParameterBagInterface $params)
    {
        // Either load from parameters or use defaults
        $this->host = $params->has('db_host') ? $params->get('db_host') : '127.0.0.1';
        $this->port = $params->has('db_port') ? (int) $params->get('db_port') : 3306;
        $this->dbname = $params->has('db_name') ? $params->get('db_name') : 'skillswap';
        $this->user = $params->has('db_user') ? $params->get('db_user') : 'root';
        $this->password = $params->has('db_password') ? $params->get('db_password') : '';
    }
    
    /**
     * Gets a PDO connection to the database
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $this->connection = new PDO($dsn, $this->user, $this->password, $options);
            } catch (PDOException $e) {
                throw new \RuntimeException("Database connection error: " . $e->getMessage());
            }
        }
        
        return $this->connection;
    }
    
    /**
     * Executes a query and returns all results
     */
    public function fetchAll(string $query, array $params = []): array
    {
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Executes a query and returns a single row
     */
    public function fetchOne(string $query, array $params = []): ?array
    {
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }
    
    /**
     * Executes a query and returns a single value
     */
    public function fetchColumn(string $query, array $params = [], int $column = 0)
    {
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn($column);
    }
    
    /**
     * Executes an INSERT, UPDATE or DELETE query
     */
    public function execute(string $query, array $params = []): int
    {
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * Inserts data into a table and returns the last inserted ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute($data);
        
        return (int) $this->getConnection()->lastInsertId();
    }
    
    /**
     * Updates data in a table and returns the number of affected rows
     */
    public function update(string $table, array $data, string $whereColumn, $whereValue): int
    {
        $setStatements = array_map(fn($col) => "$col = :$col", array_keys($data));
        
        $query = sprintf(
            "UPDATE %s SET %s WHERE %s = :whereValue",
            $table,
            implode(', ', $setStatements),
            $whereColumn
        );
        
        $params = array_merge($data, ['whereValue' => $whereValue]);
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Deletes data from a table and returns the number of affected rows
     */
    public function delete(string $table, string $whereColumn, $whereValue): int
    {
        $query = sprintf(
            "DELETE FROM %s WHERE %s = :whereValue",
            $table,
            $whereColumn
        );
        
        $stmt = $this->getConnection()->prepare($query);
        $stmt->execute(['whereValue' => $whereValue]);
        
        return $stmt->rowCount();
    }
} 