<?php
/**
 * Database Connection Class
 * 
 * Handles all database connections using PDO
 * Implements singleton pattern for efficient resource usage
 * 
 * @category Database
 * @package LU Academic Hub
 * @author Paddy Pride
 */

class Database {
    /**
     * @var PDO Database connection instance
     */
    private static $instance = null;

    /**
     * @var PDO Connection
     */
    private $connection;

    /**
     * Private constructor - prevents direct instantiation
     */
    private function __construct() {
        try {
            $this->connection = new PDO(
                DB_DSN,
                DB_USER,
                DB_PASS,
                PDO_OPTIONS
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            die('Database connection failed. Please try again later.');
        }
    }

    /**
     * Get singleton instance of Database
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Execute SELECT query
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array Query results
     */
    public function select($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Database select error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Execute query that returns single row
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array Single row result
     */
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Database select error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Execute INSERT, UPDATE, DELETE query
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return bool Success status
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Database execute error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get last inserted ID
     * 
     * @return string Last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Get number of affected rows
     * 
     * @return int Number of affected rows
     */
    public function rowCount($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Database row count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Start transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * Close connection
     */
    public function close() {
        $this->connection = null;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserializing
     */
    private function __wakeup() {}
}

?>
