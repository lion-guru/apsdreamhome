<?php
/**
 * Enhanced Database Security and Performance Configuration
 */

// Load environment variables securely
require_once __DIR__ . '/env_loader.php';
require_once __DIR__ . '/security_logger.php';

class DatabaseSecurityUpgrade {
    private $conn;
    private $logger;

    public function __construct() {
        $this->initLogger();
        $this->secureConnection();
    }

    private function initLogger() {
        // Implement secure logging mechanism
        $this->logger = new SecurityLogger('/logs/db_security.log');
    }

        private function secureConnection()
    {
        // Include config.php to access database credentials if needed
        define('DB_AUDIT_MODE', true);
        require_once dirname(__DIR__) . '/config.php';

        try {
            // Use environment variables or secure configuration management
            $host = getenv('DB_HOST') ?: DB_HOST;
            $user = getenv('DB_USER') ?: DB_USER;
            $pass = getenv('DB_PASS') ?: DB_PASSWORD;
            $name = getenv('DB_NAME') ?: DB_NAME;

            $this->conn = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4", 
                $user, 
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true  // Connection pooling
                ]
            );
        } catch (Exception $e) {
            $this->logger->logSuspiciousActivity('Database Connection Failed', $e->getMessage());
            die('Database connection error');
        }
    }

    public function preparedQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logger->logSuspiciousActivity('Query Execution Failed', json_encode(['query' => $query, 'error' => $e->getMessage()]));
            return false;
        }
    }

    public function sanitizeInput($input) {
        // Advanced input sanitization
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 1024 * 16,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    public function verifyPassword($input, $hash) {
        return password_verify($input, $hash);
    }
}


