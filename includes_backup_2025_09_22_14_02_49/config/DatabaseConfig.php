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
    /**
     * Initialize database configuration
     * 
     * @throws Exception If required configuration is missing
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        try {
            // Try to load environment variables if .env file exists
            self::loadEnvFile();
            
            // First try to get from environment variables (for backward compatibility)
            self::$host = getenv('DB_HOST') ?: (defined('DB_HOST') ? DB_HOST : 'localhost');
            self::$user = getenv('DB_USER') ?: (defined('DB_USER') ? DB_USER : 'root');
            self::$pass = getenv('DB_PASS') ?: (defined('DB_PASS') ? DB_PASS : '');
            self::$name = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : 'apsdreamhomefinal');
            self::$port = getenv('DB_PORT') ?: (defined('DB_PORT') ? DB_PORT : '3306');
            
            // Validate configuration
            if (empty(self::$host) || empty(self::$user) || empty(self::$name)) {
                throw new Exception('Database configuration is incomplete. Please check your configuration.');
            }
            
            // Define constants for backward compatibility if not already defined
            if (!defined('DB_HOST')) define('DB_HOST', self::$host);
            if (!defined('DB_USER')) define('DB_USER', self::$user);
            if (!defined('DB_PASS')) define('DB_PASS', self::$pass);
            if (!defined('DB_NAME')) define('DB_NAME', self::$name);
            if (!defined('DB_PORT')) define('DB_PORT', self::$port);
            
            self::$initialized = true;
            
        } catch (Exception $e) {
            error_log('Database configuration error: ' . $e->getMessage());
            throw new Exception('Failed to initialize database configuration. Please check your settings.');
        }
    }
    
    /**
     * Load environment variables from .env file
     */
    private static function loadEnvFile() {
        $envFile = dirname(dirname(__DIR__)) . '/.env';
        
        if (!file_exists($envFile)) {
            error_log('Warning: .env file not found at ' . $envFile);
            return;
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            error_log('Warning: Failed to read .env file');
            return;
        }
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse the line
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                
                // Set the environment variable if not already set
                if (!getenv($name)) {
                    putenv("$name=$value");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
    
    /**
     * Get a database connection
     * 
     * @return mysqli
     * @throws Exception If connection fails
     */
    /**
     * Get a database connection
     * 
     * @return mysqli
     * @throws Exception If connection fails
     */
    public static function getConnection() {
        if (self::$connection !== null) {
            return self::$connection;
        }
        
        self::init();
        
        try {
            // Create new connection
            self::$connection = new mysqli(
                self::$host,
                self::$user,
                self::$pass,
                self::$name,
                (int)self::$port
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
            throw $e; // Re-throw to allow calling code to handle the exception
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