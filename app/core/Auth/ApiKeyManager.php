<?php
/**
 * APS Dream Home - API Key Manager
 * Integrates MCP keys with existing API key system
 */
namespace App\Services;

use PDO;
use PDOException;

class ApiKeyManager {
    private $pdo;
    private static $instance = null;
    
    // Table names
    const LEGACY_API_KEYS = "api_keys";        // User API keys (existing)
    const MCP_API_KEYS = "mcp_api_keys";       // MCP/Environment keys (new)
    
    public function __construct($pdo = null) {
        if ($pdo === null) {
            // Use unified database connection from base system
            require_once __DIR__ . '/../Unified/base.php';
            $pdo = aps_db();
        }
        $this->pdo = $pdo;
    }
    
    public static function getInstance($pdo = null) {
        if (self::$instance === null) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }
    
    /**
     * Get MCP/Environment key
     */
    public function getMcpKey($keyName) {
        $stmt = $this->pdo->prepare("SELECT key_value FROM " . self::MCP_API_KEYS . " WHERE key_name = ? AND is_active = 1");
        $stmt->execute([$keyName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $this->markKeyUsed($keyName, self::MCP_API_KEYS);
            return $result["key_value"];
        }
        
        return null;
    }
    
    /**
     * Get user API key
     */
    public function getUserApiKey($apiKey) {
        $stmt = $this->pdo->prepare("SELECT * FROM " . self::LEGACY_API_KEYS . " WHERE api_key = ? AND status = 'active'");
        $stmt->execute([$apiKey]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $this->markKeyUsed($apiKey, self::LEGACY_API_KEYS);
            return $result;
        }
        
        return null;
    }
    
    /**
     * Store MCP key
     */
    public function storeMcpKey($keyName, $keyValue, $keyType, $serviceName, $description = "") {
        $table = self::MCP_API_KEYS;
        $stmt = $this->pdo->prepare("
            INSERT INTO $table (key_name, key_value, key_type, service_name, description) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            key_value = VALUES(key_value), 
            description = VALUES(description),
            updated_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$keyName, $keyValue, $keyType, $serviceName, $description]);
    }
    
    /**
     * Create user API key
     */
    public function createUserApiKey($userId, $name, $permissions = [], $rateLimit = 1000) {
        $apiKey = $this->generateApiKey();
        $table = self::LEGACY_API_KEYS;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO $table (api_key, name, user_id, permissions, rate_limit, status) 
            VALUES (?, ?, ?, ?, ?, 'active')
        ");
        
        return $stmt->execute([$apiKey, $name, $userId, json_encode($permissions), $rateLimit]) ? $apiKey : false;
    }
    
    /**
     * Mark key as used
     */
    private function markKeyUsed($keyIdentifier, $table) {
        if ($table === self::MCP_API_KEYS) {
            $stmt = $this->pdo->prepare("
                UPDATE $table 
                SET usage_count = usage_count + 1, last_used_at = CURRENT_TIMESTAMP 
                WHERE key_name = ?
            ");
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE $table 
                SET last_used_at = CURRENT_TIMESTAMP 
                WHERE api_key = ?
            ");
        }
        return $stmt->execute([$keyIdentifier]);
    }
    
    /**
     * Generate API key
     */
    private function generateApiKey() {
        return "aps_" . bin2hex(random_bytes(16));
    }
    
    /**
     * Get all MCP keys (for admin)
     */
    public function getAllMcpKeys() {
        $stmt = $this->pdo->prepare("
            SELECT key_name, service_name, key_type, description, is_active, created_at, last_used_at, usage_count 
            FROM " . self::MCP_API_KEYS . " 
            ORDER BY service_name, key_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all user API keys (for admin)
     */
    public function getAllUserApiKeys() {
        $stmt = $this->pdo->prepare("
            SELECT api_key, name, user_id, permissions, rate_limit, status, created_at, last_used_at 
            FROM " . self::LEGACY_API_KEYS . " 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get system statistics
     */
    public function getSystemStats() {
        $mcpStats = $this->pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active FROM " . self::MCP_API_KEYS)->fetch();
        $userStats = $this->pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active FROM " . self::LEGACY_API_KEYS)->fetch();
        
        return [
            "mcp_keys" => $mcpStats,
            "user_keys" => $userStats,
            "total_keys" => $mcpStats["total"] + $userStats["total"],
            "active_keys" => $mcpStats["active"] + $userStats["active"]
        ];
    }
    
    /**
     * Validate API request
     */
    public function validateApiRequest($apiKey, $requiredPermissions = []) {
        $keyInfo = $this->getUserApiKey($apiKey);
        
        if (!$keyInfo) {
            return ["valid" => false, "error" => "Invalid API key"];
        }
        
        // Check rate limit
        if ($keyInfo["rate_limit"] && $this->checkRateLimit($apiKey, $keyInfo["rate_limit"])) {
            return ["valid" => false, "error" => "Rate limit exceeded"];
        }
        
        // Check permissions
        if (!empty($requiredPermissions)) {
            $keyPermissions = json_decode($keyInfo["permissions"] ?? "[]", true);
            if (!empty(array_diff($requiredPermissions, $keyPermissions))) {
                return ["valid" => false, "error" => "Insufficient permissions"];
            }
        }
        
        return ["valid" => true, "key_info" => $keyInfo];
    }
    
    /**
     * Check rate limit
     */
    private function checkRateLimit($apiKey, $limit) {
        // Simple rate limiting - can be enhanced with Redis
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as requests 
            FROM api_requests 
            WHERE api_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$apiKey]);
        $requests = $stmt->fetchColumn();
        
        return $requests >= $limit;
    }
}
?>