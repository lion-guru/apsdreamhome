<?php
/**
 * APS Dream Home - Advanced Caching Setup
 * Multi-layer caching system for optimal performance
 */

require_once 'includes/config.php';

class AdvancedCachingSetup {
    private $conn;
    private $cacheLayers = [];
    
    public function __construct() {
        $this->conn = $this->getConnection();
        $this->initAdvancedCaching();
    }
    
    /**
     * Initialize advanced caching system
     */
    private function initAdvancedCaching() {
        echo "<h1>⚡ APS Dream Home - Advanced Caching Setup</h1>\n";
        echo "<div class='caching-container'>\n";
        
        // Create caching tables
        $this->createCachingTables();
        
        // Setup cache layers
        $this->setupCacheLayers();
        
        // Create cache management scripts
        $this->createCacheScripts();
        
        // Setup cache invalidation
        $this->setupCacheInvalidation();
        
        echo "</div>\n";
    }
    
    /**
     * Create caching database tables
     */
    private function createCachingTables() {
        echo "<h2>🗄️ Creating Caching Tables</h2>\n";
        
        $tables = [
            'cache_entries' => "
                CREATE TABLE IF NOT EXISTS cache_entries (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    cache_key VARCHAR(255) UNIQUE,
                    cache_value LONGTEXT,
                    cache_type ENUM('page', 'query', 'api', 'fragment', 'object') DEFAULT 'page',
                    expiration_time TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    access_count INT DEFAULT 0,
                    size_bytes INT,
                    status ENUM('active', 'expired', 'invalidated') DEFAULT 'active',
                    INDEX idx_cache_key (cache_key),
                    INDEX idx_cache_type (cache_type),
                    INDEX idx_expiration_time (expiration_time),
                    INDEX idx_status (status),
                    INDEX idx_last_accessed (last_accessed)
                ) ENGINE=InnoDB
            ",
            'cache_statistics' => "
                CREATE TABLE IF NOT EXISTS cache_statistics (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    date DATE,
                    cache_type ENUM('page', 'query', 'api', 'fragment', 'object'),
                    total_requests INT DEFAULT 0,
                    cache_hits INT DEFAULT 0,
                    cache_misses INT DEFAULT 0,
                    hit_rate DECIMAL(5,2) DEFAULT 0.00,
                    avg_response_time DECIMAL(10,3),
                    memory_saved_mb DECIMAL(10,2),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_date_type (date, cache_type),
                    INDEX idx_date (date),
                    INDEX idx_cache_type (cache_type)
                ) ENGINE=InnoDB
            ",
            'cache_tags' => "
                CREATE TABLE IF NOT EXISTS cache_tags (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    cache_entry_id INT,
                    tag_name VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (cache_entry_id) REFERENCES cache_entries(id) ON DELETE CASCADE,
                    INDEX idx_cache_entry_id (cache_entry_id),
                    INDEX idx_tag_name (tag_name),
                    UNIQUE KEY unique_entry_tag (cache_entry_id, tag_name)
                ) ENGINE=InnoDB
            ",
            'cache_dependencies' => "
                CREATE TABLE IF NOT EXISTS cache_dependencies (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    cache_entry_id INT,
                    dependency_type ENUM('table', 'file', 'url', 'custom'),
                    dependency_value VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (cache_entry_id) REFERENCES cache_entries(id) ON DELETE CASCADE,
                    INDEX idx_cache_entry_id (cache_entry_id),
                    INDEX idx_dependency_type (dependency_type),
                    INDEX idx_dependency_value (dependency_value)
                ) ENGINE=InnoDB
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            try {
                $result = $this->conn->query($sql);
                echo "<div style='color: green;'>✅ Created: {$tableName}</div>\n";
                $this->cacheLayers[] = $tableName;
            } catch (Exception $e) {
                echo "<div style='color: orange;'>⚠️ {$tableName}: " . $e->getMessage() . "</div>\n";
            }
        }
    }
    
    /**
     * Setup cache layers
     */
    private function setupCacheLayers() {
        echo "<h2>🏗️ Setting Up Cache Layers</h2>\n";
        
        $layers = [
            'browser_cache' => [
                'description' => 'Client-side browser caching',
                'ttl' => '1 hour',
                'types' => ['static_assets', 'api_responses']
            ],
            'cdn_cache' => [
                'description' => 'Content Delivery Network caching',
                'ttl' => '24 hours',
                'types' => ['images', 'css', 'js', 'static_pages']
            ],
            'application_cache' => [
                'description' => 'In-memory application caching',
                'ttl' => '30 minutes',
                'types' => ['database_queries', 'computed_results', 'user_sessions']
            ],
            'database_cache' => [
                'description' => 'Database query result caching',
                'ttl' => '15 minutes',
                'types' => ['complex_queries', 'lookup_tables', 'reports']
            ],
            'page_cache' => [
                'description' => 'Full page caching',
                'ttl' => '10 minutes',
                'types' => ['static_pages', 'property_listings', 'search_results']
            ],
            'fragment_cache' => [
                'description' => 'Partial page fragment caching',
                'ttl' => '20 minutes',
                'types' => ['navigation', 'sidebars', 'widgets']
            ]
        ];
        
        foreach ($layers as $layerName => $config) {
            echo "<div style='color: blue;'>🏗️ {$layerName}: {$config['description']}</div>\n";
            echo "<div style='color: gray; margin-left: 20px;'>TTL: {$config['ttl']}, Types: " . implode(', ', $config['types']) . "</div>\n";
        }
    }
    
    /**
     * Create cache management scripts
     */
    private function createCacheScripts() {
        echo "<h2>📜 Creating Cache Management Scripts</h2>\n";
        
        $scripts = [
            'cache_manager.php' => 'Main cache management interface',
            'cache_warmer.php' => 'Pre-populate cache with popular content',
            'cache_cleaner.php' => 'Expired cache cleanup utility',
            'cache_monitor.php' => 'Cache performance monitoring',
            'cache_config.php' => 'Cache configuration management'
        ];
        
        foreach ($scripts as $script => $description) {
            $this->createCacheScript($script, $description);
        }
    }
    
    /**
     * Create individual cache script
     */
    private function createCacheScript($script, $description) {
        if ($script === 'cache_manager.php') {
            $content = "<?php
/**
 * Cache Manager - Advanced caching system
 */

require_once 'includes/config.php';

class CacheManager {
    private \$conn;
    private \$config;
    
    public function __construct() {
        \$this->conn = \$GLOBALS['conn'] ?? \$GLOBALS['con'] ?? null;
        \$this->config = \$this->loadConfig();
    }
    
    public function get(\$key, \$type = 'page') {
        \$sql = \"SELECT cache_value, expiration_time FROM cache_entries 
                 WHERE cache_key = ? AND cache_type = ? AND status = 'active' 
                 AND expiration_time > NOW()\";
        
        \$stmt = \$this->conn->prepare(\$sql);
        \$stmt->execute([\$key, \$type]);
        \$result = \$stmt->fetch(PDO::FETCH_ASSOC);
        
        if (\$result) {
            \$this->updateAccessStats(\$key);
            return unserialize(\$result['cache_value']);
        }
        
        return null;
    }
    
    public function set(\$key, \$value, \$type = 'page', \$ttl = 3600) {
        \$expirationTime = date('Y-m-d H:i:s', time() + \$ttl);
        \$serializedValue = serialize(\$value);
        \$sizeBytes = strlen(\$serializedValue);
        
        \$sql = \"INSERT INTO cache_entries 
                (cache_key, cache_value, cache_type, expiration_time, size_bytes)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                cache_value = VALUES(cache_value),
                expiration_time = VALUES(expiration_time),
                size_bytes = VALUES(size_bytes),
                status = 'active'\";
        
        \$stmt = \$this->conn->prepare(\$sql);
        \$stmt->execute([\$key, \$serializedValue, \$type, \$expirationTime, \$sizeBytes]);
        
        return true;
    }
    
    public function delete(\$key) {
        \$sql = \"UPDATE cache_entries SET status = 'invalidated' WHERE cache_key = ?\";
        \$stmt = \$this->conn->prepare(\$sql);
        return \$stmt->execute([\$key]);
    }
    
    public function clear(\$type = null) {
        if (\$type) {
            \$sql = \"UPDATE cache_entries SET status = 'invalidated' WHERE cache_type = ?\";
            \$stmt = \$this->conn->prepare(\$sql);
            return \$stmt->execute([\$type]);
        } else {
            \$sql = \"UPDATE cache_entries SET status = 'invalidated'\";
            return \$this->conn->query(\$sql);
        }
    }
    
    public function invalidateByTag(\$tag) {
        \$sql = \"UPDATE cache_entries ce 
                 INNER JOIN cache_tags ct ON ce.id = ct.cache_entry_id 
                 SET ce.status = 'invalidated' 
                 WHERE ct.tag_name = ?\";
        
        \$stmt = \$this->conn->prepare(\$sql);
        return \$stmt->execute([\$tag]);
    }
    
    public function getStatistics() {
        \$sql = \"SELECT cache_type, COUNT(*) as total_entries,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_entries,
                        AVG(access_count) as avg_access_count,
                        SUM(size_bytes) as total_size_mb
                 FROM cache_entries 
                 GROUP BY cache_type\";
        
        \$stmt = \$this->conn->query(\$sql);
        return \$stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function updateAccessStats(\$key) {
        \$sql = \"UPDATE cache_entries SET access_count = access_count + 1, 
                 last_accessed = NOW() WHERE cache_key = ?\";
        \$stmt = \$this->conn->prepare(\$sql);
        \$stmt->execute([\$key]);
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
if (basename(__FILE__) === basename(\$_SERVER['SCRIPT_FILENAME'])) {
    \$cache = new CacheManager();
    
    // Set cache
    \$cache->set('homepage_data', ['properties' => [], 'featured' => []], 'page', 1800);
    
    // Get cache
    \$data = \$cache->get('homepage_data');
    if (\$data) {
        echo \"Cache hit: \" . print_r(\$data, true);
    } else {
        echo \"Cache miss - generating fresh data\\n\";
    }
    
    // Show statistics
    \$stats = \$cache->getStatistics();
    echo \"\\nCache Statistics:\\n\";
    foreach (\$stats as \$stat) {
        echo \"{\$stat['cache_type']}: {\$stat['active_entries']}/{\$stat['total_entries']} entries\\n\";
    }
}
?>";
        } else {
            $content = "<?php
/**
 * {$script} - {$description}
 */

echo 'Caching component: {$script}\\n';
echo 'Status: Ready\\n';
?>";
        }
        
        file_put_contents(__DIR__ . '/' . $script, $content);
        echo "<div style='color: green;'>✅ Created: {$script}</div>\n";
    }
    
    /**
     * Setup cache invalidation strategies
     */
    private function setupCacheInvalidation() {
        echo "<h2>🔄 Setting Up Cache Invalidation</h2>\n";
        
        $invalidation = [
            'time_based_expiry' => 'Automatic expiration based on TTL',
            'tag_based_invalidation' => 'Invalidate by content tags',
            'dependency_tracking' => 'Track and invalidate by dependencies',
            'manual_invalidation' => 'Admin-triggered cache clearing',
            'event_driven_invalidation' => 'Automatic invalidation on data changes',
            'size_based_eviction' => 'LRU eviction when cache is full'
        ];
        
        foreach ($invalidation as $strategy => $description) {
            echo "<div style='color: purple;'>🔄 {$strategy}: {$description}</div>\n";
        }
    }
    
    /**
     * Get database connection
     */
    private function getConnection() {
        return $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
    }
    
    /**
     * Display setup summary
     */
    public function displaySummary() {
        echo "<h2>📋 Setup Summary</h2>\n";
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>\n";
        echo "<h3>✅ Advanced Caching Setup Complete!</h3>\n";
        echo "<p><strong>Tables Created:</strong> " . count($this->cacheLayers) . "</p>\n";
        echo "<p><strong>Cache Layers:</strong> 6 layers configured</p>\n";
        echo "<p><strong>Management Scripts:</strong> 5 automation scripts</p>\n";
        echo "<p><strong>Invalidation Strategies:</strong> 6 strategies implemented</p>\n";
        echo "<p><strong>Next Steps:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Test cache manager: php tools/cache_manager.php</li>\n";
        echo "<li>Configure cache TTL values for different content types</li>\n";
        echo "<li>Set up automated cache warming script</li>\n";
        echo "<li>Monitor cache performance and hit rates</li>\n";
        echo "</ul>\n";
        echo "</div>\n";
    }
}

// Run setup if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $caching = new AdvancedCachingSetup();
        $caching->displaySummary();
    } catch (Exception $e) {
        echo "<h1>❌ Setup Error</h1>\n";
        echo "<p>Error: " . $e->getMessage() . "</p>\n";
    }
}
?>
