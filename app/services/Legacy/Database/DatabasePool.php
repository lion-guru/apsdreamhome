<?php

namespace App\Services\Legacy\Database;

use App\Core\App;
use Exception;

/**
 * Database Connection Pool Configuration
 */
class DatabasePool {
    private static $pool = [];
    private static $maxConnections = 10;
    private static $minConnections = 2;
    private static $currentConnections = 0;
    
    public static function getConnection() {
        try {
            return App::database()->getConnection();
        } catch (Exception $e) {
            error_log("DatabasePool error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function releaseConnection($connection) {
        // In the new architecture, connections are managed by the App::database() singleton
        // No explicit release is needed for the shared connection.
    }
    
    private static function createConnection() {
        return App::database()->getConnection();
    }
    
    private static function isValidConnection($connection) {
        try {
            $connection->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private static function waitForConnection() {
        // Simple retry logic - in production, use proper semaphore
        $maxWait = 5; // 5 seconds max wait
        $waitTime = 0;
        
        while ($waitTime < $maxWait) {
            if (!empty(self::$pool)) {
                $connection = array_pop(self::$pool);
                if (self::isValidConnection($connection)) {
                    return $connection;
                }
            }
            
            usleep(100000); // Wait 100ms
            $waitTime += 0.1;
        }
        
        throw new Exception("Database connection timeout");
    }
    
    private static function closeConnection($connection) {
        try {
            $connection = null;
        } catch (Exception $e) {
            // Connection already closed
        }
    }
    
    public static function initializePool() {
        // Create minimum connections
        for ($i = 0; $i < self::$minConnections; $i++) {
            try {
                $connection = self::createConnection();
                self::$pool[] = $connection;
            } catch (Exception $e) {
                error_log("Failed to initialize pool connection: " . $e->getMessage());
            }
        }
    }
    
    public static function getPoolStats() {
        return [
            'active_connections' => self::$currentConnections,
            'pooled_connections' => count(self::$pool),
            'max_connections' => self::$maxConnections,
            'min_connections' => self::$minConnections
        ];
    }
}
