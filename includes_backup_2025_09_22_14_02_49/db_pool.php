<?php
/**
 * Database Connection Pool
 * Provides efficient database connection management
 */

class DatabaseConnectionPool {
    private static $instance = null;
    private $connections = [];
    private $maxConnections = 10;
    private $inUseConnections = [];
    
    private function __construct() {
        // Initialize connection pool
        $config = ConfigManager::getInstance();
        $this->maxConnections = $config->get('DB_MAX_CONNECTIONS', 10);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        // Check for available connection in the pool
        foreach ($this->connections as $key => $conn) {
            if (!isset($this->inUseConnections[$key])) {
                $this->inUseConnections[$key] = true;
                return ['connection' => $conn, 'key' => $key];
            }
        }
        
        // Create new connection if under max limit
        if (count($this->connections) < $this->maxConnections) {
            try {
                $conn = getDbConnection();
                $key = count($this->connections);
                $this->connections[$key] = $conn;
                $this->inUseConnections[$key] = true;
                return ['connection' => $conn, 'key' => $key];
            } catch (Exception $e) {
                error_log("Failed to create new connection in pool: " . $e->getMessage());
                throw $e;
            }
        }
        
        // Wait for an available connection
        throw new Exception("Connection pool exhausted. Try again later.");
    }
    
    public function releaseConnection($key) {
        if (isset($this->inUseConnections[$key])) {
            unset($this->inUseConnections[$key]);
            return true;
        }
        return false;
    }
    
    public function closeAll() {
        foreach ($this->connections as $conn) {
            $conn->close();
        }
        $this->connections = [];
        $this->inUseConnections = [];
    }
}