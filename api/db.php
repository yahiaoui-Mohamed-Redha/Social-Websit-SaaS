<?php
require_once __DIR__ . '/../config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false
                ]
            );
            
            // Test the connection immediately
            $this->conn->query("SELECT 1");
            
        } catch (PDOException $e) {
            $this->logDatabaseError($e);
            throw new Exception("Database connection failed: " . $e->getMessage());
        }

        return $this->conn;
    }

    private function logDatabaseError(PDOException $e) {
        $errorMsg = "PDOException: " . $e->getMessage() . "\n";
        $errorMsg .= "Error Code: " . $e->getCode() . "\n";
        $errorMsg .= "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
        $errorMsg .= "Trace:\n" . $e->getTraceAsString() . "\n";
        
        error_log($errorMsg);
        
        // For development only - remove in production
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "<pre>" . htmlspecialchars($errorMsg) . "</pre>";
        }
    }
}

