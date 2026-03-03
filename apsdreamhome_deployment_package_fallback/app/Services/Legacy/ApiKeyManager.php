<?php

namespace App\Services\Legacy;
/**
 * API Key Manager
 * Handles API key generation, validation, and management
 */

class ApiKeyManager {
    private $db;
    private $logger;

    public function __construct($db = null, $logger = null) {
        $this->db = $db ?? \App\Core\App::database();
        $this->logger = $logger;
        $this->createApiKeysTable();
    }

    /**
     * Create API keys table
     */
    private function createApiKeysTable() {
        $sql = "CREATE TABLE IF NOT EXISTS api_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            api_key VARCHAR(64) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            user_id INT,
            permissions JSON,
            rate_limit INT DEFAULT 1000,
            daily_limit INT DEFAULT 10000,
            monthly_limit INT DEFAULT 100000,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            last_used_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            usage_count INT DEFAULT 0,
            daily_usage INT DEFAULT 0,
            monthly_usage INT DEFAULT 0,
            last_reset_date DATE DEFAULT CURRENT_DATE,
            FOREIGN KEY (user_id) REFERENCES user(uid) ON DELETE CASCADE,
            INDEX idx_api_key (api_key),
            INDEX idx_user_id (user_id)
        )";

        $this->db->execute($sql);
    }

    /**
     * Generate a new API key
     */
    public function generateKey($name, $userId = null, $permissions = [], $expiresAt = null, $rateLimit = 1000) {
        // Generate a secure random key
        $plainApiKey = 'aps_' . \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));
        // Hash the API key for secure storage (consistent with Auth.php)
        $hashedApiKey = \hash('sha256', $plainApiKey);

        $permissionsJson = \json_encode($permissions);

        if ($expiresAt) {
            $expiresAtFormatted = date('Y-m-d H:i:s', strtotime($expiresAt));
        } else {
            $expiresAtFormatted = null;
        }

        $sql = "INSERT INTO api_keys (api_key, name, user_id, permissions, rate_limit, expires_at)
                VALUES (?, ?, ?, ?, ?, ?)";

        $result = $this->db->execute($sql, [$hashedApiKey, $name, $userId, $permissionsJson, $rateLimit, $expiresAtFormatted]);

        if ($result) {
            if ($this->logger) {
                // Check if logger is a SecurityLogger or a function
                if (method_exists($this->logger, 'log')) {
                    $this->logger->log("API key generated for: $name", 'info', 'api');
                }
            }
            return $plainApiKey;
        }

        return false;
    }

    /**
     * Validate API key
     * @param string $apiKey The API key to validate
     * @param array $requiredPermissions Optional array of required permissions
     * @return array|false Key data if valid, false otherwise
     */
    public function validateKey($apiKey, $requiredPermissions = []) {
        // Hash the incoming API key for comparison with stored hash
        $hashedApiKey = \hash('sha256', $apiKey);

        // Check if key exists and is active
        $sql = "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1";
        $keyData = $this->db->fetchOne($sql, [$hashedApiKey]);

        if (!$keyData) {
            return false;
        }

        // Check if key has expired
        if ($keyData['expires_at'] && \strtotime($keyData['expires_at']) < \time()) {
            return false;
        }

        // Check rate limits
        if (!$this->checkRateLimit($hashedApiKey)) {
            return false;
        }

        // Check permissions if required
        if (!empty($requiredPermissions) && !$this->checkPermissions($keyData, $requiredPermissions)) {
            return false;
        }

        // Update usage statistics
        $this->updateUsageStats($hashedApiKey);

        return $keyData;
    }

    /**
     * Check if API key has required permissions
     * @param array $keyData API key data from database
     * @param array $requiredPermissions Array of required permissions
     * @return bool True if key has required permissions, false otherwise
     */
    private function checkPermissions($keyData, $requiredPermissions) {
        if (empty($requiredPermissions)) {
            return true;
        }

        // Get key permissions from database
        $keyPermissions = json_decode($keyData['permissions'] ?? '[]', true);

        // If key has wildcard permission, allow all
        if (in_array('*', $keyPermissions)) {
            return true;
        }

        // Check if key has all required permissions
        foreach ($requiredPermissions as $requiredPermission) {
            if (!in_array($requiredPermission, $keyPermissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit($hashedApiKey) {
        $sql = "SELECT rate_limit, daily_limit, monthly_limit, daily_usage, monthly_usage
                FROM api_keys WHERE api_key = ?";
        $keyData = $this->db->fetchOne($sql, [$hashedApiKey]);

        if (!$keyData) {
            return false;
        }

        // Check daily limit
        if ($keyData['daily_usage'] >= $keyData['daily_limit']) {
            return false;
        }

        // Check monthly limit
        if ($keyData['monthly_usage'] >= $keyData['monthly_limit']) {
            return false;
        }

        return true;
    }

    /**
     * Update usage statistics
     */
    private function updateUsageStats($hashedApiKey) {
        $currentDate = \date('Y-m-d');

        // Check if we need to reset daily/monthly counters
        $sql = "SELECT last_reset_date, daily_usage, monthly_usage
                FROM api_keys WHERE api_key = ?";
        $keyData = $this->db->fetchOne($sql, [$hashedApiKey]);

        if (!$keyData) {
            return;
        }

        $newDailyUsage = $keyData['daily_usage'] + 1;
        $newMonthlyUsage = $keyData['monthly_usage'] + 1;

        // Reset daily counter if it's a new day
        if ($keyData['last_reset_date'] != $currentDate) {
            $newDailyUsage = 1;
        }

        // Reset monthly counter if it's a new month
        $currentMonth = \date('Y-m');
        $lastMonth = \date('Y-m', \strtotime($keyData['last_reset_date']));

        if ($currentMonth != $lastMonth) {
            $newMonthlyUsage = 1;
        }

        // Update the statistics
        $updateSql = "UPDATE api_keys
                      SET usage_count = usage_count + 1,
                          daily_usage = ?,
                          monthly_usage = ?,
                          last_used_at = NOW(),
                          last_reset_date = ?
                      WHERE api_key = ?";

        $this->db->execute($updateSql, [$newDailyUsage, $newMonthlyUsage, $currentDate, $hashedApiKey]);
    }

    /**
     * Get all API keys for a user
     */
    public function getUserKeys($userId) {
        $sql = "SELECT id, name, api_key, permissions, rate_limit, daily_limit, monthly_limit,
                       created_at, expires_at, last_used_at, is_active, usage_count
                FROM api_keys
                WHERE user_id = ?
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Deactivate API key
     */
    public function deactivateKey($apiKey) {
        // Hash the API key for comparison with stored hash
        $hashedApiKey = \hash('sha256', $apiKey);

        $sql = "UPDATE api_keys SET is_active = 0 WHERE api_key = ?";
        $result = $this->db->execute($sql, [$hashedApiKey]);

        if ($result && $this->logger) {
            if (\method_exists($this->logger, 'log')) {
                $this->logger->log("API key deactivated: $apiKey", 'info', 'api');
            }
        }

        return $result;
    }

    /**
     * Delete API key
     */
    public function deleteKey($apiKey) {
        // Hash the API key for comparison with stored hash
        $hashedApiKey = \hash('sha256', $apiKey);

        $sql = "DELETE FROM api_keys WHERE api_key = ?";
        $result = $this->db->execute($sql, [$hashedApiKey]);

        if ($result && $this->logger) {
            if (\method_exists($this->logger, 'log')) {
                $this->logger->log("API key deleted: $apiKey", 'warning', 'api');
            }
        }

        return $result;
    }

    /**
     * Get API key statistics
     */
    public function getKeyStats($apiKey) {
        // Hash the API key for comparison with stored hash
        $hashedApiKey = \hash('sha256', $apiKey);

        $sql = "SELECT usage_count, daily_usage, monthly_usage, last_used_at,
                       created_at, rate_limit, daily_limit, monthly_limit
                FROM api_keys WHERE api_key = ?";

        return $this->db->fetchOne($sql, [$hashedApiKey]);
    }
}
?>
