<?php

namespace App\Config;

/**
 * Unified Database Configuration
 * Combines the best of both mysqli and PDO approaches
 * Provides environment-based configuration with fallbacks
 */
class DatabaseConfig
{
    private static $instance = null;
    private $config = [];
    
    private function __construct()
    {
        $this->loadConfiguration();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load configuration from environment variables with fallbacks
     */
    private function loadConfiguration(): void
    {
        $this->config = [
            'host' => $this->getEnvVar('DB_HOST', 'localhost'),
            'port' => $this->getEnvVar('DB_PORT', '3306'),
            'database' => $this->getEnvVar('DB_NAME', 'apsdreamhome'),
            'username' => $this->getEnvVar('DB_USER', 'root'),
            'password' => $this->getEnvVar('DB_PASS', ''),
            'charset' => $this->getEnvVar('DB_CHARSET', 'utf8mb4'),
            'collation' => $this->getEnvVar('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => $this->getEnvVar('DB_PREFIX', ''),
            
            // Performance settings
            'pdo_options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => true,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ],
            
            // Query optimization
            'query_cache_enabled' => $this->getEnvVar('QUERY_CACHE_ENABLED', 'true') === 'true',
            'slow_query_threshold' => (float) $this->getEnvVar('SLOW_QUERY_THRESHOLD', '1.0'),
            'max_cache_size' => (int) $this->getEnvVar('MAX_CACHE_SIZE', '1000'),
            
            // Connection pooling
            'connection_pool_size' => (int) $this->getEnvVar('CONNECTION_POOL_SIZE', '10'),
            'connection_timeout' => (int) $this->getEnvVar('CONNECTION_TIMEOUT', '30'),
            
            // Security settings
            'ssl_enabled' => $this->getEnvVar('DB_SSL_ENABLED', 'false') === 'true',
            'ssl_verify' => $this->getEnvVar('DB_SSL_VERIFY', 'true') === 'true',
        ];
        
        // Validate required configuration
        $this->validateConfiguration();
    }
    
    /**
     * Get environment variable with multiple fallback methods
     */
    private function getEnvVar(string $key, string $default = null): string
    {
        // Method 1: Check $_ENV superglobal
        if (isset($_ENV[$key]) && $_ENV[$key] !== false) {
            return $_ENV[$key];
        }
        
        // Method 2: Check getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Method 3: Check .env file
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue; // Skip comments
                if (strpos($line, '=') !== false) {
                    list($envKey, $envValue) = explode('=', $line, 2);
                    if (trim($envKey) === $key) {
                        return trim($envValue);
                    }
                }
            }
        }
        
        // Method 4: Check config.php constants (for backward compatibility)
        $constantName = str_replace('DB_', '', $key);
        if (defined($constantName)) {
            return constant($constantName);
        }
        
        return $default;
    }
    
    /**
     * Validate configuration settings
     */
    private function validateConfiguration(): void
    {
        $required = ['host', 'database', 'username'];
        foreach ($required as $key) {
            if (empty($this->config[$key])) {
                throw new \Exception("Database configuration error: Missing required setting '{$key}'");
            }
        }
        
        // Validate numeric settings
        if ($this->config['slow_query_threshold'] <= 0) {
            $this->config['slow_query_threshold'] = 1.0;
        }
        
        if ($this->config['max_cache_size'] <= 0) {
            $this->config['max_cache_size'] = 1000;
        }
    }
    
    /**
     * Get configuration value
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Get PDO connection string (DSN)
     */
    public function getDsn(): string
    {
        $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";
        
        if ($this->config['ssl_enabled']) {
            $dsn .= ";sslmode=require";
            if ($this->config['ssl_verify']) {
                $dsn .= ";sslverify=1";
            }
        }
        
        return $dsn;
    }
    
    /**
     * Get all configuration as array
     */
    public function getAll(): array
    {
        return $this->config;
    }
    
    /**
     * Check if configuration is valid for connection
     */
    public function isValid(): bool
    {
        return !empty($this->config['host']) && 
               !empty($this->config['database']) && 
               !empty($this->config['username']);
    }
    
    /**
     * Get environment information for debugging
     */
    public function getEnvironmentInfo(): array
    {
        return [
            'environment_loaded' => true,
            'config_source' => $this->detectConfigSource(),
            'available_methods' => $this->getAvailableMethods(),
            'warnings' => $this->getWarnings()
        ];
    }
    
    private function detectConfigSource(): string
    {
        if (getenv('DB_HOST') !== false) return 'environment';
        if (file_exists(dirname(__DIR__, 2) . '/.env')) return '.env file';
        if (defined('DB_HOST')) return 'constants';
        return 'defaults';
    }
    
    private function getAvailableMethods(): array
    {
        $methods = [];
        if (function_exists('getenv')) $methods[] = 'getenv()';
        if (!empty($_ENV)) $methods[] = '$_ENV';
        if (file_exists(dirname(__DIR__, 2) . '/.env')) $methods[] = '.env file';
        if (defined('DB_HOST')) $methods[] = 'constants';
        return $methods;
    }
    
    private function getWarnings(): array
    {
        $warnings = [];
        
        if ($this->config['password'] === '') {
            $warnings[] = 'Database password is empty - this is a security risk';
        }
        
        if ($this->config['host'] === 'localhost' && php_uname('n') !== 'localhost') {
            $warnings[] = 'Using localhost in production environment - consider using specific host';
        }
        
        if ($this->config['query_cache_enabled'] && $this->config['max_cache_size'] > 5000) {
            $warnings[] = 'Large query cache size may impact memory usage';
        }
        
        return $warnings;
    }
}