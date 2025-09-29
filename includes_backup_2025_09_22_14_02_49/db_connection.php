<?php
/**
 * Centralized Database Connection Management
 * Provides a secure and consistent way to establish database connections
 */

require_once __DIR__ . '/validator.php';
require_once __DIR__ . '/config_manager.php';

function loadEnvironmentVariables() {
    $envPath = __DIR__ . '/config/.env';
    if (!file_exists($envPath)) {
        error_log("Environment configuration file not found: $envPath");
        return false;
    }

    $env = @parse_ini_file($envPath);
    if ($env === false) {
        error_log("Failed to parse environment configuration file: $envPath");
        return false;
    }

    foreach ($env as $key => $value) {
        $sanitizedValue = trim($value);
        // Set environment variables
        putenv("$key=$sanitizedValue");
        $_ENV[$key] = $sanitizedValue;
    }

    return true;
}

function getDbConnection() {
    // Logging function for database connection errors
    $logDatabaseError = function($message, $context = []) {
        $logFile = __DIR__ . '/../logs/database_connection.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}\n";
        
        if (!empty($context)) {
            $logEntry .= "Context: " . json_encode($context, JSON_PRETTY_PRINT) . "\n";
        }
        
        error_log($logEntry, 3, $logFile);
    };

    // Load environment variables with enhanced error tracking
    try {
        if (!loadEnvironmentVariables()) {
            $logDatabaseError('Failed to load environment configuration', [
                'env_file_path' => __DIR__ . '/config/.env'
            ]);
            throw new Exception('Environment configuration failed');
        }

        // Get database configuration
        $config = ConfigManager::getInstance();
        $host = $config->get('DB_HOST', 'localhost');
        $user = $config->get('DB_USER', 'root');
        $pass = $config->get('DB_PASS', '');
        $dbname = $config->get('DB_NAME', 'apsdreamhomefinal');
        $maxConnections = $config->get('DB_MAX_CONNECTIONS', 10);
        $connectionTimeout = $config->get('DB_CONNECTION_TIMEOUT', 30);

        // Validate critical database parameters
        if (empty($host) || empty($user) || empty($dbname)) {
            $logDatabaseError('Missing Critical Database Configuration Parameters', [
                'host' => $host,
                'user' => $user,
                'database' => $dbname
            ]);
            throw new Exception('Incomplete database configuration');
        }

        // Validate database parameters using validator
        $validator = validator();
        $connectionParams = [
            'host' => $host,
            'user' => $user,
            'database' => $dbname,
            'max_connections' => $maxConnections,
            'timeout' => $connectionTimeout
        ];

        $validationRules = [
            'host' => 'required|regex:/^[a-zA-Z0-9.-]+$/',
            'user' => 'required|alphanumeric',
            'database' => 'required|alphanumeric',
            'max_connections' => 'required|numeric|min:1|max:100',
            'timeout' => 'required|numeric|min:1|max:60'
        ];

        if (!$validator->validate($connectionParams, $validationRules)) {
            $errors = $validator->getErrors();
            $logDatabaseError('Invalid Database Configuration', [
                'validation_errors' => $errors,
                'connection_params' => $connectionParams
            ]);
            throw new Exception("Invalid database configuration: " . json_encode($errors));
        }

        // Create connection with enhanced security and comprehensive error handling
        try {
            // Set connection timeout
            ini_set('mysql.connect_timeout', $connectionTimeout);

            // Create the connection first
            $conn = new mysqli($host, $user, $pass, $dbname);
            
            // Then set SSL options if needed
            $sslOptions = []; // Default to empty array
            if (defined('MYSQLI_CLIENT_SSL')) {
                $sslKey = $config->get('DB_SSL_KEY', '');
                $sslCert = $config->get('DB_SSL_CERT', '');
                $sslCA = $config->get('DB_SSL_CA', '');
                $sslCAPath = $config->get('DB_SSL_CAPATH', '');
                $sslCipher = $config->get('DB_SSL_CIPHER', '');

                // Only set SSL if at least one option is provided
                if (!empty($sslKey) || !empty($sslCert) || !empty($sslCA) || !empty($sslCAPath)) {
                    $conn->ssl_set(
                        $sslKey ?: null,
                        $sslCert ?: null,
                        $sslCA ?: null,
                        $sslCAPath ?: null,
                        $sslCipher ?: null
                    );
                }
            }

            // Check connection with detailed error logging
            if ($conn->connect_error) {
                                $logDatabaseError('Database Connection Error', [
                    'error_number' => $conn->connect_errno,
                    'error_message' => $conn->connect_error,
                    'host' => $host,
                    'database' => $dbname
                ]);
                throw new Exception("Database Connection Error: " . $conn->connect_error);
            }

            
            // Set character set with error handling
            if (!$conn->set_charset("utf8mb4")) {
                                $logDatabaseError('Character Set Configuration Error', [
                    'error_message' => $conn->error
                ]);
                throw new Exception("Failed to set character set: " . $conn->error);
            }
            
            // Prevent potential SQL injection and set strict mode
            $securityQueries = [
                "SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION,NO_BACKSLASH_ESCAPES'",
                "SET SESSION max_join_size = 1000000",
                "SET SESSION group_concat_max_len = 1024"
            ];

            try {
                foreach ($securityQueries as $query) {
                    if (!$conn->query($query)) {
                                                $logDatabaseError('Security Query Execution Failed', [
                            'error_message' => $conn->error
                        ]);
                    }
                }
            } catch (Exception $e) {
                $logDatabaseError('Security Query Execution Failed', [
                    'error_message' => $e->getMessage()
                ]);
            }

            // Enable query cache with error tracking
            $queryCacheEnabled = getenv('QUERY_CACHE_ENABLED');
            if ($queryCacheEnabled === 'true') {
                $cacheQueries = [
                    "SET SESSION query_cache_type = ON",
                    "SET SESSION query_cache_size = 1048576", // 1MB
                    "SET SESSION query_cache_limit = 1048576"
                ];

                foreach ($cacheQueries as $query) {
                    if (!$conn->query($query)) {
                        $logDatabaseError('Query Cache Configuration Failed', [
                            'query' => $query,
                            'error_message' => $conn->error
                        ]);
                    }
                }
            }

            return $conn;
        } catch (Exception $e) {
            // Log detailed error
            error_log("Database Connection Failed: " . $e->getMessage());
            
            // Throw exception for higher-level error handling
            throw $e;
        }
    } catch (Exception $e) {
        // Log detailed error
        $logDatabaseError('Database Connection Error', [
            'error_message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

/**
 * Perform comprehensive database connection test
 * 
 * @return array Test results with detailed diagnostics
 */
function testDatabaseConnection() {
    $testResults = [
        'connection_status' => false,
        'env_vars_loaded' => false,
        'connection_time' => 0,
        'errors' => []
    ];

    try {
        // Start timing
        $startTime = microtime(true);

        // Load environment variables
        $testResults['env_vars_loaded'] = loadEnvironmentVariables();
        if (!$testResults['env_vars_loaded']) {
            $testResults['errors'][] = 'Failed to load environment variables';
            return $testResults;
        }

        // Attempt database connection
        $conn = getDbConnection();

        // Perform basic query to test connection
        $testQuery = "SELECT 1 AS connection_test";
        $result = $conn->query($testQuery);

        if ($result === false) {
            $testResults['errors'][] = 'Test query failed: ' . $conn->error;
        } else {
            $testResults['connection_status'] = true;
        }

        // Calculate connection time
        $testResults['connection_time'] = round(microtime(true) - $startTime, 4);

        // Additional diagnostics
        $testResults['server_info'] = [
            'server_version' => $conn->server_info,
            'host_info' => $conn->host_info,
            'protocol_version' => $conn->protocol_version
        ];

        // Close connection
        $conn->close();

    } catch (Exception $e) {
        $testResults['errors'][] = $e->getMessage();
    }

    return $testResults;
}

/**
 * Check if an IP address is within a given IP range
 * 
 * @param string $ip The IP address to check
 * @param string $range The IP range in CIDR notation or exact IP
 * @return bool True if IP is in range, false otherwise
 */
function ipInRange($ip, $range) {
    if (strpos($range, '/') !== false) {
        list($range, $netmask) = explode('/', $range, 2);
        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
        $netmaskDecimal = ~ $wildcardDecimal;
        return (($ipDecimal & $netmaskDecimal) === ($rangeDecimal & $netmaskDecimal));
    } else {
        return $ip === $range;
    }
}

// Ensure the function is part of a class or has a proper context
