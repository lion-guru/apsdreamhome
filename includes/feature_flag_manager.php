<?php
/**
 * Advanced Feature Flag and Dynamic Configuration Management System
 * Provides robust feature toggling, configuration management, and runtime adaptability
 */

// require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/config_manager.php';
require_once __DIR__ . '/event_monitor.php';

class FeatureFlagManager {
    // Feature Flag Types
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_TARGETING = 'targeting';

    // Configuration Storage Modes
    public const STORAGE_MEMORY = 'memory';
    public const STORAGE_DATABASE = 'database';
    public const STORAGE_REMOTE = 'remote';

    // Feature Flag Statuses
    public const STATUS_ENABLED = 'enabled';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_CONDITIONAL = 'conditional';

    // Feature Flags and Configurations
    private $featureFlags = [];
    private $dynamicConfigurations = [];

    // System Dependencies
    private $logger;
    private $config;
    private $eventMonitor;

    // Configuration Parameters
    private $storageMode;
    private $refreshInterval;
    private $remoteConfigUrl;

    // Caching and Performance
    private $cachedFlags = [];
    private $lastRefreshTime;

    public function __construct(
        $storageMode = self::STORAGE_MEMORY,
        $refreshInterval = 300 // 5 minutes
    ) {
        $this->logger = new Logger();
        $this->config = ConfigManager::getInstance();
        $this->eventMonitor = new EventMonitor();

        $this->storageMode = $storageMode;
        $this->refreshInterval = $refreshInterval;

        // Load initial configuration
        $this->loadConfiguration();
    }

    /**
     * Load feature flag and configuration settings
     */
    private function loadConfiguration() {
        $this->storageMode = $this->config->get(
            'FEATURE_FLAG_STORAGE_MODE', 
            self::STORAGE_MEMORY
        );
        $this->refreshInterval = $this->config->get(
            'FEATURE_FLAG_REFRESH_INTERVAL', 
            300
        );
        $this->remoteConfigUrl = $this->config->get(
            'REMOTE_CONFIG_URL', 
            null
        );

        // Load predefined feature flags
        $this->loadPredefinedFlags();
    }

    /**
     * Load predefined feature flags from configuration
     */
    private function loadPredefinedFlags() {
        $predefinedFlags = [
            'advanced_security' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'description' => 'Enable advanced security features'
            ],
            'performance_monitoring' => [
                'type' => self::TYPE_PERCENTAGE,
                'default' => 50,
                'description' => 'Percentage of requests to monitor'
            ],
            'user_registration' => [
                'type' => self::TYPE_TARGETING,
                'default' => false,
                'rules' => [
                    'email_domain' => ['@company.com'],
                    'user_role' => ['admin', 'manager']
                ]
            ]
        ];

        foreach ($predefinedFlags as $key => $flag) {
            $this->registerFeatureFlag($key, $flag);
        }
    }

    /**
     * Register a new feature flag
     * 
     * @param string $key Feature flag identifier
     * @param array $config Feature flag configuration
     */
    public function registerFeatureFlag($key, array $config) {
        $this->featureFlags[$key] = array_merge([
            'type' => self::TYPE_BOOLEAN,
            'status' => self::STATUS_ENABLED,
            'created_at' => time(),
            'updated_at' => time()
        ], $config);

        // Log feature flag registration
        $this->eventMonitor->logEvent('FEATURE_FLAG_REGISTERED', [
            'key' => $key,
            'type' => $config['type']
        ]);
    }

    /**
     * Check if a feature flag is enabled
     * 
     * @param string $key Feature flag identifier
     * @param array $context Optional context for targeted flags
     * @return bool Whether the feature is enabled
     */
    public function isFeatureEnabled(
        $key, 
        array $context = []
    ) {
        // Check cache first
        if (isset($this->cachedFlags[$key]) && 
            time() - $this->lastRefreshTime < $this->refreshInterval) {
            return $this->cachedFlags[$key];
        }

        // Refresh configuration if needed
        $this->refreshConfiguration();

        // Check if feature flag exists
        if (!isset($this->featureFlags[$key])) {
            $this->logger->warning("Feature flag not found", ['key' => $key]);
            return false;
        }

        $flag = $this->featureFlags[$key];

        // Evaluate based on flag type
        $enabled = match($flag['type']) {
            self::TYPE_BOOLEAN => $flag['default'] ?? false,
            self::TYPE_PERCENTAGE => $this->evaluatePercentageFlag($flag),
            self::TYPE_TARGETING => $this->evaluateTargetingFlag($flag, $context),
            default => false
        };

        // Cache the result
        $this->cachedFlags[$key] = $enabled;
        $this->lastRefreshTime = time();

        return $enabled;
    }

    /**
     * Evaluate percentage-based feature flag
     * 
     * @param array $flag Feature flag configuration
     * @return bool Whether the feature is enabled
     */
    private function evaluatePercentageFlag(array $flag) {
        $percentage = $flag['default'] ?? 0;
        $randomValue = mt_rand(0, 100);
        return $randomValue <= $percentage;
    }

    /**
     * Evaluate targeting-based feature flag
     * 
     * @param array $flag Feature flag configuration
     * @param array $context User context
     * @return bool Whether the feature is enabled
     */
    private function evaluateTargetingFlag(
        array $flag, 
        array $context
    ) {
        if (!isset($flag['rules'])) {
            return $flag['default'] ?? false;
        }

        foreach ($flag['rules'] as $attribute => $allowedValues) {
            if (!isset($context[$attribute]) || 
                !in_array($context[$attribute], $allowedValues)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set dynamic configuration
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public function setDynamicConfiguration($key, $value) {
        $this->dynamicConfigurations[$key] = [
            'value' => $value,
            'set_at' => time()
        ];

        // Log configuration change
        $this->eventMonitor->logEvent('DYNAMIC_CONFIG_UPDATED', [
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Get dynamic configuration
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed Configuration value
     */
    public function getDynamicConfiguration(
        $key, 
        $default = null
    ) {
        return $this->dynamicConfigurations[$key]['value'] ?? $default;
    }

    /**
     * Refresh configuration based on storage mode
     */
    private function refreshConfiguration() {
        switch ($this->storageMode) {
            case self::STORAGE_DATABASE:
                $this->refreshFromDatabase();
                break;
            case self::STORAGE_REMOTE:
                $this->refreshFromRemoteSource();
                break;
        }
    }

    /**
     * Refresh configuration from database
     */
    private function refreshFromDatabase() {
        // Implement database-driven configuration refresh
        // This would typically involve querying a configuration table
    }

    /**
     * Refresh configuration from remote source
     */
    private function refreshFromRemoteSource() {
        if (!$this->remoteConfigUrl) {
            return;
        }

        try {
            $remoteConfig = file_get_contents($this->remoteConfigUrl);
            $parsedConfig = json_decode($remoteConfig, true);

            // Update feature flags and configurations
            if (is_array($parsedConfig)) {
                foreach ($parsedConfig as $key => $config) {
                    $this->registerFeatureFlag($key, $config);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Remote Configuration Refresh Failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate feature flag report
     * 
     * @return array Feature flag and configuration report
     */
    public function generateReport() {
        return [
            'feature_flags' => $this->featureFlags,
            'dynamic_configurations' => $this->dynamicConfigurations,
            'storage_mode' => $this->storageMode,
            'last_refresh' => $this->lastRefreshTime
        ];
    }

    /**
     * Demonstrate feature flag and configuration management
     */
    public function demonstrateFeatureFlags() {
        // Register a new feature flag
        $this->registerFeatureFlag('beta_user_dashboard', [
            'type' => self::TYPE_TARGETING,
            'default' => false,
            'rules' => [
                'user_role' => ['admin', 'beta_tester']
            ]
        ]);

        // Set dynamic configuration
        $this->setDynamicConfiguration(
            'max_concurrent_users', 
            100
        );

        // Check feature flag with context
        $context = [
            'user_role' => 'admin',
            'email_domain' => '@company.com'
        ];

        $betaDashboardEnabled = $this->isFeatureEnabled(
            'beta_user_dashboard', 
            $context
        );

        echo "Beta Dashboard Enabled: " . 
            ($betaDashboardEnabled ? 'Yes' : 'No') . "\n";

        // Get dynamic configuration
        $maxUsers = $this->getDynamicConfiguration(
            'max_concurrent_users', 
            50
        );

        echo "Max Concurrent Users: $maxUsers\n";

        // Generate and display report
        $report = $this->generateReport();
        print_r($report);
    }
}

// Global helper function for feature flag management
function feature_flags($storageMode = FeatureFlagManager::STORAGE_MEMORY) {
    return new FeatureFlagManager($storageMode);
}
