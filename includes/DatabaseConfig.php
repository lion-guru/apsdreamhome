<?php
/**
 * DatabaseConfig Class
 * Provides centralized database configuration and connection management
 * for the entire APS Dream Homes application.
 */
class DatabaseConfig {
    // Database connection parameters
    private static $host;
    private static $user;
    private static $pass;
    private static $name;
    private static $port;
    
    // Connection instances (for connection pooling)
    private static $connection = null;
    private static $initialized = false;
    
    /**
     * Initialize database configuration from environment variables
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        
        // Load environment variables if .env file exists
        self::loadEnvFile();
        
        // Set database parameters from environment variables with fallbacks
        self::$host = getenv('DB_HOST') ?: 'localhost';
        self::$user = getenv('DB_USER') ?: 'root';
        self::$pass = getenv('DB_PASS') ?: '';
        self::$name = getenv('DB_NAME') ?: 'realestatephp';
        self::$port = getenv('DB_PORT') ?: '3306';
        
        // Define constants for backward compatibility
        if (!defined('DB_HOST')) define('DB_HOST', self::$host);
        if (!defined('DB_USER')) define('DB_USER', self::$user);
        if (!defined('DB_PASS')) define('DB_PASS', self::$pass);
        if (!defined('DB_NAME')) define('DB_NAME', self::$name);
        if (!defined('DB_PORT')) define('DB_PORT', self::$port);
        
        self::$initialized = true;
    }
    
    /**
     * Load environment variables from .env file
     */
    private static function loadEnvFile() {
        $envFile = dirname(__DIR__) . '/.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
    
    /**
     * Get database connection with error handling
     * @return mysqli|null Database connection or null on failure
     */
    public static function getConnection() {
        // Initialize configuration if not already done
        if (!self::$initialized) {
            self::init();
        }
        
        // Return existing connection if available
        if (self::$connection !== null) {
            return self::$connection;
        }
        
        try {
            // Create new connection
            self::$connection = new mysqli(
                self::$host,
                self::$user,
                self::$pass,
                self::$name,
                self::$port
            );
            
            // Check connection
            if (self::$connection->connect_error) {
                throw new Exception("Database connection failed: " . self::$connection->connect_error);
            }
            
            // Set charset to prevent injection
            if (!self::$connection->set_charset("utf8mb4")) {
                throw new Exception("Error setting charset: " . self::$connection->error);
            }
            
            // Set SQL mode for stricter SQL syntax
            $sql_mode = "STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION";
            if (!self::$connection->query("SET SESSION sql_mode = '$sql_mode'")) {
                throw new Exception("Error setting SQL mode: " . self::$connection->error);
            }
            
            return self::$connection;
        } catch (Exception $e) {
            // Log error
            error_log("Database connection exception: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Close the database connection
     */
    public static function closeConnection() {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }
    
    /**
     * Get database host
     * @return string Database host
     */
    public static function getHost() {
        if (!self::$initialized) self::init();
        return self::$host;
    }
    
    /**
     * Get database user
     * @return string Database user
     */
    public static function getUser() {
        if (!self::$initialized) self::init();
        return self::$user;
    }
    
    /**
     * Get database name
     * @return string Database name
     */
    public static function getName() {
        if (!self::$initialized) self::init();
        return self::$name;
    }
    
    /**
     * Get database port
     * @return string Database port
     */
    public static function getPort() {
        if (!self::$initialized) self::init();
        return self::$port;
    }
}

// Initialize database configuration
DatabaseConfig::init();

// Create global connection variables for backward compatibility
$con = DatabaseConfig::getConnection();
$conn = $con;