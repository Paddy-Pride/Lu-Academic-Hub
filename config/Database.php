<?php
/**
 * Database Configuration & Connection Handler
 * Manages PDO connections with error handling
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'lu_academic_hub';
    private $user = 'root';
    private $pass = '';
    private $pdo;

    public function connect() {
        $this->pdo = null;

        try {
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8';
            $this->pdo = new PDO($dsn, $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $this->pdo;
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            die(json_encode([
                'status' => 'error',
                'message' => 'Database connection failed'
            ]));
        }
    }

    public function getPDO() {
        if (!$this->pdo) {
            $this->connect();
        }
        return $this->pdo;
    }

    public function closeConnection() {
        $this->pdo = null;
    }
}

// Initialize database connection
$db = new Database();
$pdo = $db->connect();
?>