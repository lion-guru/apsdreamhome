<?php

namespace App\Services\Legacy;
/**
 * Comprehensive Security Configuration Management
 * Provides dynamic security configuration and policy enforcement
 */
class SecurityConfiguration {
    // Default security policies
    private const DEFAULT_POLICIES = [
        'password_complexity' => [
            'min_length' => 12,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_number' => true,
            'require_special_char' => true
        ],
        'session_management' => [
            'timeout' => 1800, // 30 minutes
            'concurrent_sessions' => 3,
            'regenerate_interval' => 300 // 5 minutes
        ],
        'two_factor_auth' => [
            'enabled' => true,
            'methods' => ['totp', 'email']
        ],
        'ip_restrictions' => [
            'enabled' => true,
            'whitelist_mode' => false
        ],
        'security_headers' => [
            'x_frame_options' => 'DENY',
            'x_xss_protection' => '1; mode=block',
            'content_security_policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'"
        ]
    ];

    /**
     * Get current security configuration
     * @return array
     */
    public static function getCurrentConfiguration() {
        try {
            $db = \App\Core\App::database();

            $sql = "
                SELECT configuration_key, configuration_value
                FROM system_configurations
                WHERE category = 'SECURITY'
            ";
            $results = $db->fetchAll($sql);

            $configs = [];
            foreach ($results as $row) {
                $configs[$row['configuration_key']] = $row['configuration_value'];
            }

            // Merge with default policies
            $mergedConfig = array_replace_recursive(
                self::DEFAULT_POLICIES,
                json_decode($configs['security_policies'] ?? '{}', true)
            );

            return $mergedConfig;
        } catch (Exception $e) {
            error_log('Security configuration retrieval error: ' . $e->getMessage());
            return self::DEFAULT_POLICIES;
        }
    }

    /**
     * Update security configuration
     * @param array $newConfig
     * @return bool
     */
    public static function updateConfiguration($newConfig) {
        // Validate configuration
        $validatedConfig = self::validateConfiguration($newConfig);

        try {
            $db = \App\Core\App::database();

            // Begin transaction
            $db->beginTransaction();

            // Update security policies
            $sql = "
                INSERT INTO system_configurations
                (configuration_key, configuration_value, category)
                VALUES ('security_policies', ?, 'SECURITY')
                ON DUPLICATE KEY UPDATE
                configuration_value = ?
            ";
            $db->execute($sql, [
                json_encode($validatedConfig),
                json_encode($validatedConfig)
            ]);

            // Log configuration change
            AdminLogger::log('SECURITY_CONFIG_UPDATED', [
                'updated_by' => $_SESSION['admin_username'] ?? 'SYSTEM',
                'changes' => array_keys($validatedConfig)
            ]);

            // Commit transaction
            $db->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            if (isset($db)) $db->rollBack();

            AdminLogger::logError('SECURITY_CONFIG_UPDATE_ERROR', [
                'message' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Validate security configuration
     * @param array $config
     * @return array
     */
    private static function validateConfiguration($config) {
        $validatedConfig = [];

        // Validate password complexity
        if (isset($config['password_complexity'])) {
            $validatedConfig['password_complexity'] = array_merge(
                self::DEFAULT_POLICIES['password_complexity'],
                array_intersect_key(
                    $config['password_complexity'],
                    self::DEFAULT_POLICIES['password_complexity']
                )
            );
        }

        // Validate session management
        if (isset($config['session_management'])) {
            $validatedConfig['session_management'] = array_merge(
                self::DEFAULT_POLICIES['session_management'],
                array_intersect_key(
                    $config['session_management'],
                    self::DEFAULT_POLICIES['session_management']
                )
            );
        }

        // Validate two-factor authentication
        if (isset($config['two_factor_auth'])) {
            $validatedConfig['two_factor_auth'] = array_merge(
                self::DEFAULT_POLICIES['two_factor_auth'],
                array_intersect_key(
                    $config['two_factor_auth'],
                    self::DEFAULT_POLICIES['two_factor_auth']
                )
            );
        }

        // Validate IP restrictions
        if (isset($config['ip_restrictions'])) {
            $validatedConfig['ip_restrictions'] = array_merge(
                self::DEFAULT_POLICIES['ip_restrictions'],
                array_intersect_key(
                    $config['ip_restrictions'],
                    self::DEFAULT_POLICIES['ip_restrictions']
                )
            );
        }

        // Validate security headers
        if (isset($config['security_headers'])) {
            $validatedConfig['security_headers'] = array_merge(
                self::DEFAULT_POLICIES['security_headers'],
                array_intersect_key(
                    $config['security_headers'],
                    self::DEFAULT_POLICIES['security_headers']
                )
            );
        }

        return $validatedConfig;
    }

    /**
     * Apply security headers
     */
    public static function applySecurityHeaders() {
        $config = self::getCurrentConfiguration();
        $headers = $config['security_headers'] ?? self::DEFAULT_POLICIES['security_headers'];

        foreach ($headers as $header => $value) {
            $headerName = str_replace('_', '-', ucwords($header, '_'));
            header("{$headerName}: {$value}");
        }
    }

    /**
     * Check if two-factor authentication is required
     * @param string $userId
     * @return bool
     */
    public static function isTwoFactorRequired($userId) {
        $config = self::getCurrentConfiguration();

        if (!$config['two_factor_auth']['enabled']) {
            return false;
        }

        try {
            $db = \App\Core\App::database();

            $sql = "
                SELECT two_factor_enabled
                FROM users
                WHERE id = ?
            ";
            $userData = $db->fetch($sql, [$userId]);

            return $userData['two_factor_enabled'] ?? false;
        } catch (Exception $e) {
            error_log('Two-factor check error: ' . $e->getMessage());
            return false;
        }
    }
}

// Global helper functions
function get_security_config() {
    return SecurityConfiguration::getCurrentConfiguration();
}

function update_security_config($config) {
    return SecurityConfiguration::updateConfiguration($config);
}

function apply_security_headers() {
    SecurityConfiguration::applySecurityHeaders();
}
