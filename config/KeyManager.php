<?php
/**
 * APS Dream Home - Key Manager Class
 * Secure key management and retrieval
 */
class KeyManager {
    private $pdo;
    private static $instance = null;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public static function getInstance($pdo = null) {
        if (self::$instance === null) {
            if ($pdo === null) {
                // Use default database connection
                $host = "localhost";
                $user = "root";
                $password = "";
                $database = "apsdreamhome";
                $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }
    
    /**
     * Get API key by name
     */
    public function getKey($keyName) {
        $stmt = $this->pdo->prepare("SELECT key_value FROM api_keys WHERE key_name = ? AND is_active = 1");
        $stmt->execute([$keyName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result["key_value"] : null;
    }
    
    /**
     * Get all keys for a service
     */
    public function getKeysByService($serviceName) {
        $stmt = $this->pdo->prepare("SELECT key_name, key_value, key_type FROM api_keys WHERE service_name = ? AND is_active = 1");
        $stmt->execute([$serviceName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Store or update a key
     */
    public function storeKey($keyName, $keyValue, $keyType, $serviceName, $description = "") {
        $stmt = $this->pdo->prepare("
            INSERT INTO api_keys (key_name, key_value, key_type, service_name, description) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            key_value = VALUES(key_value), 
            description = VALUES(description),
            updated_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$keyName, $keyValue, $keyType, $serviceName, $description]);
    }
    
    /**
     * Mark key as used
     */
    public function markKeyUsed($keyName) {
        $stmt = $this->pdo->prepare("
            UPDATE api_keys 
            SET usage_count = usage_count + 1, last_used_at = CURRENT_TIMESTAMP 
            WHERE key_name = ?
        ");
        return $stmt->execute([$keyName]);
    }
    
    /**
     * Deactivate a key
     */
    public function deactivateKey($keyName) {
        $stmt = $this->pdo->prepare("UPDATE api_keys SET is_active = 0 WHERE key_name = ?");
        return $stmt->execute([$keyName]);
    }
    
    /**
     * Get all active keys (for admin)
     */
    public function getAllKeys() {
        $stmt = $this->pdo->prepare("
            SELECT key_name, service_name, key_type, description, is_active, created_at, last_used_at, usage_count 
            FROM api_keys 
            ORDER BY service_name, key_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get key usage statistics
     */
    public function getKeyStats() {
        $stmt = $this->pdo->prepare("
            SELECT 
                service_name,
                COUNT(*) as total_keys,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_keys,
                SUM(usage_count) as total_usage
            FROM api_keys 
            GROUP BY service_name
            ORDER BY service_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>