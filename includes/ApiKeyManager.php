<?php
/**
 * API Key Manager
 * Handles API key generation, validation, and management
 */

class ApiKeyManager {
    private $conn;
    private $logger;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
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
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_api_key (api_key),
            INDEX idx_user_id (user_id)
        )";

        $this->conn->query($sql);
    }

    /**
     * Generate a new API key
     */
    public function generateKey($name, $userId = null, $permissions = [], $expiresAt = null, $rateLimit = 1000) {
        // Generate a secure random key
        $apiKey = 'aps_' . bin2hex(random_bytes(32));

        $permissionsJson = json_encode($permissions);

        if ($expiresAt) {
            $expiresAtFormatted = date('Y-m-d H:i:s', strtotime($expiresAt));
        } else {
            $expiresAtFormatted = null;
        }

        $sql = "INSERT INTO api_keys (api_key, name, user_id, permissions, rate_limit, expires_at)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssissi", $apiKey, $name, $userId, $permissionsJson, $rateLimit, $expiresAtFormatted);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            if ($this->logger) {
                $this->logger->log("API key generated for: $name", 'info', 'api');
            }
            return $apiKey;
        }

        return false;
    }

    /**
     * Validate API key
     */
    public function validateKey($apiKey) {
        // Check if key exists and is active
        $sql = "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        $keyData = $result->fetch_assoc();
        $stmt->close();

        // Check if key has expired
        if ($keyData['expires_at'] && strtotime($keyData['expires_at']) < time()) {
            return false;
        }

        // Check rate limits
        if (!$this->checkRateLimit($apiKey)) {
            return false;
        }

        // Update usage statistics
        $this->updateUsageStats($apiKey);

        return $keyData;
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit($apiKey) {
        $sql = "SELECT rate_limit, daily_limit, monthly_limit, daily_usage, monthly_usage
                FROM api_keys WHERE api_key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $keyData = $result->fetch_assoc();
        $stmt->close();

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
    private function updateUsageStats($apiKey) {
        $currentDate = date('Y-m-d');

        // Check if we need to reset daily/monthly counters
        $sql = "SELECT last_reset_date, daily_usage, monthly_usage
                FROM api_keys WHERE api_key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $keyData = $result->fetch_assoc();
        $stmt->close();

        $updates = [];
        $newDailyUsage = $keyData['daily_usage'] + 1;
        $newMonthlyUsage = $keyData['monthly_usage'] + 1;

        // Reset daily counter if it's a new day
        if ($keyData['last_reset_date'] != $currentDate) {
            $newDailyUsage = 1;
        }

        // Reset monthly counter if it's a new month
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime($keyData['last_reset_date']));

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

        $stmt = $this->conn->prepare($updateSql);
        $stmt->bind_param("iiss", $newDailyUsage, $newMonthlyUsage, $currentDate, $apiKey);
        $stmt->execute();
        $stmt->close();
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

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $keys = [];
        while ($row = $result->fetch_assoc()) {
            $keys[] = $row;
        }
        $stmt->close();

        return $keys;
    }

    /**
     * Deactivate API key
     */
    public function deactivateKey($apiKey) {
        $sql = "UPDATE api_keys SET is_active = 0 WHERE api_key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $apiKey);
        $result = $stmt->execute();
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("API key deactivated: $apiKey", 'warning', 'api');
        }

        return $result;
    }

    /**
     * Delete API key
     */
    public function deleteKey($apiKey) {
        $sql = "DELETE FROM api_keys WHERE api_key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $apiKey);
        $result = $stmt->execute();
        $stmt->close();

        if ($result && $this->logger) {
            $this->logger->log("API key deleted: $apiKey", 'info', 'api');
        }

        return $result;
    }

    /**
     * Get API key statistics
     */
    public function getKeyStats($apiKey) {
        $sql = "SELECT usage_count, daily_usage, monthly_usage, last_used_at,
                       created_at, rate_limit, daily_limit, monthly_limit
                FROM api_keys WHERE api_key = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();

        return $stats;
    }
}
?>
