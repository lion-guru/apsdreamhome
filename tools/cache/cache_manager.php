<?php
/**
 * Cache Manager - Advanced caching system
 */

require_once 'includes/config.php';

class CacheManager {
    private $conn;
    private $config;
    
    public function __construct() {
        $this->conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
        $this->config = $this->loadConfig();
    }
    
    public function get($key, $type = 'page') {
        $sql = "SELECT cache_value, expiration_time FROM cache_entries 
                 WHERE cache_key = ? AND cache_type = ? AND status = 'active' 
                 AND expiration_time > NOW()";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$key, $type]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $this->updateAccessStats($key);
            return unserialize($result['cache_value']);
        }
        
        return null;
    }
    
    public function set($key, $value, $type = 'page', $ttl = 3600) {
        $expirationTime = date('Y-m-d H:i:s', time() + $ttl);
        $serializedValue = serialize($value);
        $sizeBytes = strlen($serializedValue);
        
        $sql = "INSERT INTO cache_entries 
                (cache_key, cache_value, cache_type, expiration_time, size_bytes)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                cache_value = VALUES(cache_value),
                expiration_time = VALUES(expiration_time),
                size_bytes = VALUES(size_bytes),
                status = 'active'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$key, $serializedValue, $type, $expirationTime, $sizeBytes]);
        
        return true;
    }
    
    public function delete($key) {
        $sql = "UPDATE cache_entries SET status = 'invalidated' WHERE cache_key = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$key]);
    }
    
    public function clear($type = null) {
        if ($type) {
            $sql = "UPDATE cache_entries SET status = 'invalidated' WHERE cache_type = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$type]);
        } else {
            $sql = "UPDATE cache_entries SET status = 'invalidated'";
            return $this->conn->query($sql);
        }
    }
    
    public function invalidateByTag($tag) {
        $sql = "UPDATE cache_entries ce 
                 INNER JOIN cache_tags ct ON ce.id = ct.cache_entry_id 
                 SET ce.status = 'invalidated' 
                 WHERE ct.tag_name = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$tag]);
    }
    
    public function getStatistics() {
        $sql = "SELECT cache_type, COUNT(*) as total_entries,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_entries,
                        AVG(access_count) as avg_access_count,
                        SUM(size_bytes) as total_size_mb
                 FROM cache_entries 
                 GROUP BY cache_type";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function updateAccessStats($key) {
        $sql = "UPDATE cache_entries SET access_count = access_count + 1, 
                 last_accessed = NOW() WHERE cache_key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$key]);
    }
    
    private function loadConfig() {
        return [
            'default_ttl' => 3600,
            'max_size_mb' => 1024,
            'cleanup_interval' => 300,
            'compression' => true
        ];
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $cache = new CacheManager();
    
    // Set cache
    $cache->set('homepage_data', ['properties' => [], 'featured' => []], 'page', 1800);
    
    // Get cache
    $data = $cache->get('homepage_data');
    if ($data) {
        echo "Cache hit: " . print_r($data, true);
    } else {
        echo "Cache miss - generating fresh data\n";
    }
    
    // Show statistics
    $stats = $cache->getStatistics();
    echo "\nCache Statistics:\n";
    foreach ($stats as $stat) {
        echo "{$stat['cache_type']}: {$stat['active_entries']}/{$stat['total_entries']} entries\n";
    }
}
?>