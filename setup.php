<?php
/**
 * APS Dream Home Project Initialization Script
 * Comprehensive setup and configuration management
 */
class ProjectInitializer {
    // Configuration paths
    private const CONFIG_FILES = [
        '.env',
        'config.json',
        'database/config.php'
    ];

    // Required PHP extensions
    private const REQUIRED_EXTENSIONS = [
        'mysqli',
        'openssl',
        'json',
        'curl',
        'mbstring',
        'opcache'
    ];

    // Required system tools
    private const REQUIRED_TOOLS = [
        'composer',
        'git'
    ];

    // Dependency configuration
    private const DEPENDENCIES = [
        'phpmailer/phpmailer' => '^6.8',
        'twilio/sdk' => '^7.4',
        'firebase/php-jwt' => '^6.4',
        'monolog/monolog' => '^3.3'
    ];

    // Database migration scripts
    private const MIGRATION_SCRIPTS = [
        'database/migrations/20250514_001_auth_tables.sql',
        'database/migrations/20250514_002_security_tables.sql',
        'database/migrations/20250514_003_email_queue.sql',
        'database/migrations/20250514_004_sms_queue.sql'
    ];

    // Initialization steps
    private $steps = [];
    private $errors = [];

    public function __construct() {
        $this->initializeSteps();
    }

    /**
     * Define initialization steps
     */
    private function initializeSteps() {
        $this->steps = [
            'check_environment' => function() {
                return $this->checkEnvironmentRequirements();
            },
            'validate_config' => function() {
                return $this->validateConfigFiles();
            },
            'install_dependencies' => function() {
                return $this->installComposerDependencies();
            },
            'setup_database' => function() {
                return $this->setupDatabase();
            },
            'configure_security' => function() {
                return $this->configureSecurity();
            },
            'setup_cron_jobs' => function() {
                return $this->setupCronJobs();
            }
        ];
    }

    /**
     * Run project initialization
     * @return bool
     */
    public function initialize() {
        foreach ($this->steps as $step => $callback) {
            try {
                $result = $callback();
                if (!$result) {
                    $this->logError("Initialization step failed: {$step}");
                    return false;
                }
            } catch (Exception $e) {
                $this->logError("Error in {$step}: " . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * Check PHP environment requirements
     * @return bool
     */
    private function checkEnvironmentRequirements() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            $this->logError('PHP 8.1+ is required');
            return false;
        }

        // Check required extensions
        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            if (!extension_loaded($ext)) {
                $this->logError("Extension {$ext} is not loaded");
                return false;
            }
        }

        // Check system tools
        foreach (self::REQUIRED_TOOLS as $tool) {
            if (!$this->checkSystemTool($tool)) {
                $this->logError("System tool {$tool} not found");
                return false;
            }
        }

        return true;
    }

    /**
     * Validate configuration files
     * @return bool
     */
    private function validateConfigFiles() {
        foreach (self::CONFIG_FILES as $file) {
            $path = dirname(__FILE__) . '/' . $file;
            if (!file_exists($path)) {
                $this->logError("Configuration file missing: {$file}");
                return false;
            }
        }

        // Additional config validation logic
        $env_config = $this->loadEnvConfig();
        if (!$this->validateEnvConfig($env_config)) {
            return false;
        }

        return true;
    }

    /**
     * Install Composer dependencies
     * @return bool
     */
    private function installComposerDependencies() {
        // Prepare composer.json update
        $composer_path = dirname(__FILE__) . '/composer.json';
        $composer_config = json_decode(file_get_contents($composer_path), true);

        // Update dependencies
        $composer_config['require'] = array_merge(
            $composer_config['require'] ?? [],
            self::DEPENDENCIES
        );

        // Write updated composer.json
        file_put_contents(
            $composer_path, 
            json_encode($composer_config, JSON_PRETTY_PRINT)
        );

        // Run composer install
        $output = [];
        $return_var = 0;
        exec('composer install', $output, $return_var);

        if ($return_var !== 0) {
            $this->logError('Composer dependency installation failed');
            return false;
        }

        return true;
    }

    /**
     * Setup database and run migrations
     * @return bool
     */
    private function setupDatabase() {
        // Load database configuration
        $db_config = $this->loadDatabaseConfig();

        // Establish database connection
        $conn = new mysqli(
            $db_config['host'], 
            $db_config['username'], 
            $db_config['password'], 
            $db_config['database']
        );

        if ($conn->connect_error) {
            $this->logError('Database connection failed: ' . $conn->connect_error);
            return false;
        }

        // Run migration scripts
        foreach (self::MIGRATION_SCRIPTS as $script) {
            $migration_path = dirname(__FILE__) . '/' . $script;
            if (!$this->runMigrationScript($conn, $migration_path)) {
                $conn->close();
                return false;
            }
        }

        $conn->close();
        return true;
    }

    /**
     * Configure security settings
     * @return bool
     */
    private function configureSecurity() {
        // Generate secure secret key
        $secret_key = bin2hex(random_bytes(32));

        // Update .env file with new secret key
        $env_path = dirname(__FILE__) . '/.env';
        $env_contents = file_get_contents($env_path);
        $env_contents = preg_replace(
            '/APP_SECRET_KEY=.*/', 
            "APP_SECRET_KEY={$secret_key}", 
            $env_contents
        );
        file_put_contents($env_path, $env_contents);

        return true;
    }

    /**
     * Setup cron jobs for background tasks
     * @return bool
     */
    private function setupCronJobs() {
        $cron_jobs = [
            '*/5 * * * * php ' . dirname(__FILE__) . '/scripts/process_email_queue.php',
            '*/5 * * * * php ' . dirname(__FILE__) . '/scripts/process_sms_queue.php',
            '0 1 * * * php ' . dirname(__FILE__) . '/scripts/security_cleanup.php'
        ];

        // Write crontab
        $crontab_file = dirname(__FILE__) . '/crontab.txt';
        file_put_contents($crontab_file, implode("\n", $cron_jobs));

        return true;
    }

    /**
     * Check if a system tool is available
     * @param string $tool Tool name
     * @return bool
     */
    private function checkSystemTool($tool) {
        exec("which {$tool}", $output, $return_var);
        return $return_var === 0;
    }

    /**
     * Load environment configuration
     * @return array
     */
    private function loadEnvConfig() {
        $env_path = dirname(__FILE__) . '/.env';
        $env_contents = file_get_contents($env_path);
        
        $config = [];
        foreach (explode("\n", $env_contents) as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $config[trim($parts[0])] = trim($parts[1]);
            }
        }

        return $config;
    }

    /**
     * Validate environment configuration
     * @param array $config Configuration array
     * @return bool
     */
    private function validateEnvConfig($config) {
        $required_keys = [
            'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME',
            'APP_SECRET_KEY', 'APP_ENV'
        ];

        foreach ($required_keys as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                $this->logError("Missing required configuration: {$key}");
                return false;
            }
        }

        return true;
    }

    /**
     * Load database configuration
     * @return array
     */
    private function loadDatabaseConfig() {
        $env_config = $this->loadEnvConfig();
        return [
            'host' => $env_config['DB_HOST'],
            'username' => $env_config['DB_USER'],
            'password' => $env_config['DB_PASS'],
            'database' => $env_config['DB_NAME']
        ];
    }

    /**
     * Run database migration script
     * @param mysqli $conn Database connection
     * @param string $script_path Path to migration script
     * @return bool
     */
    private function runMigrationScript($conn, $script_path) {
        if (!file_exists($script_path)) {
            $this->logError("Migration script not found: {$script_path}");
            return false;
        }

        $migration_sql = file_get_contents($script_path);
        
        // Split SQL into individual statements
        $statements = array_filter(explode(';', $migration_sql));
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;

            if (!$conn->query($statement)) {
                $this->logError("Migration error: " . $conn->error);
                return false;
            }
        }

        return true;
    }

    /**
     * Log initialization errors
     * @param string $message Error message
     */
    private function logError($message) {
        $this->errors[] = $message;
        error_log("[ProjectInitializer] {$message}");
    }

    /**
     * Get initialization errors
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
}

// Run initialization
$initializer = new ProjectInitializer();
$success = $initializer->initialize();

if ($success) {
    echo "Project initialization completed successfully!\n";
} else {
    echo "Project initialization failed. Errors:\n";
    print_r($initializer->getErrors());
    exit(1);
}
