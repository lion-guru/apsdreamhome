<?php
/**
 * API Key Manager
 * Handles API key generation, validation, and management
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/security_logger.php';

class ApiKeyManager {
    private $con;
    private $logger;
    private $table = '// SECURITY: Sensitive information removeds';

    public function __construct($database_connection = null, $security_logger = null) {
        $this->con = $database_connection ?? getDbConnection();
        $this->logger = $security_logger ?? new SecurityLogger();
        $this->initializeTable();
    }

    /**
     * Initialize API keys table if it doesn't exist
     */
    private function initializeTable() {
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            // SECURITY: Sensitive information removed VARCHAR(64) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            user_id INT,
            permissions JSON,
            rate_limit INT DEFAULT 1000,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            last_used_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_// SECURITY: Sensitive information removed (// SECURITY: Sensitive information removed),
            INDEX idx_user_id (user_id)
        )";
        
        $this->con->query($query);
    }

    /**
     * Generate a new API key
     */
    public function generateKey($name, $userId, $permissions = [], $expiresAt = null, $rateLimit = 1000) {
        // Generate a secure random key
        $apiKey = bin2hex(random_bytes(32));
        
        // Insert the key into database
        $query = "INSERT INTO {$this->table} 
                 (// SECURITY: Sensitive information removed, name, user_id, permissions, rate_limit, expires_at) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->con->prepare($query);
        $permissionsJson = json_encode($permissions);
        $stmt->bind_param("ssisss", $apiKey, $name, $userId, $permissionsJson, $rateLimit, $expiresAt);
        
        if ($stmt->execute()) {
            $this->logger->logConfigChange(
                $userId,
                '// SECURITY: Sensitive information removed_generated',
                null,
                ['key_id' => $stmt->insert_id, 'name' => $name]
            );
            
            return [
                'key' => $apiKey,
                'id' => $stmt->insert_id,
                'name' => $name
            ];
        }
        
        return false;
    }

    /**
     * Validate an API key
     */
    public function validateKey($apiKey, $requiredPermissions = []) {
        $query = "SELECT * FROM {$this->table} WHERE // SECURITY: Sensitive information removed = ? AND is_active = TRUE";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $this->logger->logSuspiciousActivity('invalid_// SECURITY: Sensitive information removed', [
                'key' => substr($apiKey, 0, 8) . '...'
            ]);
            return false;
        }
        
        $keyData = $result->fetch_assoc();
        
        // Check if key has expired
        if ($keyData['expires_at'] && strtotime($keyData['expires_at']) < time()) {
            $this->logger->logSuspiciousActivity('expired_// SECURITY: Sensitive information removed', [
                'key_id' => $keyData['id'],
                'name' => $keyData['name']
            ]);
            return false;
        }
        
        // Check permissions
        if (!empty($requiredPermissions)) {
            $keyPermissions = json_decode($keyData['permissions'], true);
            foreach ($requiredPermissions as $permission) {
                if (!in_array($permission, $keyPermissions)) {
                    $this->logger->logSuspiciousActivity('insufficient_permissions', [
                        'key_id' => $keyData['id'],
                        'name' => $keyData['name'],
                        'required' => $permission
                    ]);
                    return false;
                }
            }
        }
        
        // Update last used timestamp
        $this->updateLastUsed($keyData['id']);
        
        return $keyData;
    }

    /**
     * Update last used timestamp
     */
    private function updateLastUsed($keyId) {
        $query = "UPDATE {$this->table} SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $keyId);
        $stmt->execute();
    }

    /**
     * Revoke an API key
     */
    public function revokeKey($keyId, $userId) {
        $query = "UPDATE {$this->table} SET is_active = FALSE WHERE id = ? AND user_id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("ii", $keyId, $userId);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $this->logger->logConfigChange(
                $userId,
                '// SECURITY: Sensitive information removed_revoked',
                'active',
                'inactive'
            );
            return true;
        }
        
        return false;
    }

    /**
     * List API keys for a user
     */
    public function listKeys($userId) {
        $query = "SELECT id, name, created_at, expires_at, last_used_at, is_active, 
                        rate_limit, permissions 
                 FROM {$this->table} 
                 WHERE user_id = ?
                 ORDER BY created_at DESC";
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $keys = [];
        while ($row = $result->fetch_assoc()) {
            $row['permissions'] = json_decode($row['permissions'], true);
            $keys[] = $row;
        }
        
        return $keys;
    }

    /**
     * Update API key settings
     */
    public function updateKey($keyId, $userId, $updates) {
        $allowedFields = ['name', 'permissions', 'rate_limit', 'expires_at', 'is_active'];
        $updates = array_intersect_key($updates, array_flip($allowedFields));
        
        if (empty($updates)) {
            return false;
        }
        
        $setClauses = [];
        $params = [];
        $types = '';
        
        foreach ($updates as $field => $value) {
            $setClauses[] = "{$field} = ?";
            $params[] = $field === 'permissions' ? json_encode($value) : $value;
            $types .= $field === 'rate_limit' || $field === 'is_active' ? 'i' : 's';
        }
        
        $query = "UPDATE {$this->table} 
                 SET " . implode(', ', $setClauses) . " 
                 WHERE id = ? AND user_id = ?";
        
        $params[] = $keyId;
        $params[] = $userId;
        $types .= 'ii';
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $this->logger->logConfigChange(
                $userId,
                '// SECURITY: Sensitive information removed_updated',
                null,
                ['key_id' => $keyId, 'updates' => $updates]
            );
            return true;
        }
        
        return false;
    }

    /**
     * Clean up expired and unused API keys
     */
    public function cleanup($daysUnused = 90) {
        $query = "UPDATE {$this->table} 
                 SET is_active = FALSE 
                 WHERE 
                    (expires_at IS NOT NULL AND expires_at < CURRENT_TIMESTAMP)
                    OR (last_used_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ? DAY))";
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $daysUnused);
        return $stmt->execute();
    }
}

// Create global API key manager instance
$apiKeyManager = new ApiKeyManager($con ?? null, $securityLogger ?? null);

