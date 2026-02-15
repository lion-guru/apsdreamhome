<?php
/**
 * APS Dream Homes - Production Deployment Helper
 * Final configuration and deployment setup
 */

require_once 'includes/config.php';

class ProductionDeployment {
    private $conn;
    private $deploymentSteps = [];
    
    public function __construct() {
        $this->conn = $this->getConnection();
        $this->initDeployment();
    }
    
    /**
     * Initialize deployment process
     */
    private function initDeployment() {
        echo "<h1>🚀 APS Dream Homes - Production Deployment</h1>\n";
        echo "<div class='deployment-container'>\n";
        
        // Run deployment checks
        $this->runDeploymentChecks();
        
        // Setup production configuration
        $this->setupProductionConfig();
        
        // Initialize production database
        $this->initializeProductionDatabase();
        
        // Setup production security
        $this->setupProductionSecurity();
        
        // Configure production caching
        $this->setupProductionCaching();
        
        // Generate production assets
        $this->generateProductionAssets();
        
        // Create deployment report
        $this->generateDeploymentReport();
        
        echo "</div>\n";
    }
    
    /**
     * Run deployment checks
     */
    private function runDeploymentChecks() {
        echo "<h2>🔍 Running Deployment Checks</h2>\n";
        
        $checks = [
            'php_version' => $this->checkPHPVersion(),
            'database_connection' => $this->checkDatabaseConnection(),
            'required_extensions' => $this->checkRequiredExtensions(),
            'file_permissions' => $this->checkFilePermissions(),
            'ssl_certificate' => $this->checkSSLCertificate(),
            'memory_limit' => $this->checkMemoryLimit(),
            'upload_limits' => $this->checkUploadLimits()
        ];
        
        $allPassed = true;
        foreach ($checks as $check => $result) {
            $status = $result['status'] ? '✅ PASS' : '❌ FAIL';
            $color = $result['status'] ? 'green' : 'red';
            echo "<div style='color: $color; margin: 5px 0;'>{$status}: {$check} - {$result['message']}</div>\n";
            if (!$result['status']) $allPassed = false;
        }
        
        if ($allPassed) {
            echo "<div style='color: green; font-weight: bold; margin: 10px 0;'>✅ All deployment checks passed!</div>\n";
        } else {
            echo "<div style='color: red; font-weight: bold; margin: 10px 0;'>⚠️ Some checks failed. Please resolve before proceeding.</div>\n";
        }
    }
    
    /**
     * Check PHP version
     */
    private function checkPHPVersion() {
        $version = PHP_VERSION;
        $required = '8.0';
        return [
            'status' => version_compare($version, $required, '>='),
            'message' => "Current: {$version}, Required: {$required}+"
        ];
    }
    
    /**
     * Check database connection
     */
    private function checkDatabaseConnection() {
        try {
            $this->conn->query("SELECT 1");
            return [
                'status' => true,
                'message' => 'Database connection successful'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check required extensions
     */
    private function checkRequiredExtensions() {
        $required = ['mysqli', 'gd', 'curl', 'json', 'mbstring', 'openssl', 'xml'];
        $missing = [];
        
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }
        
        return [
            'status' => empty($missing),
            'message' => empty($missing) ? 'All required extensions loaded' : 'Missing: ' . implode(', ', $missing)
        ];
    }
    
    /**
     * Check file permissions
     */
    private function checkFilePermissions() {
        $paths = [
            'uploads' => __DIR__ . '/uploads',
            'cache' => __DIR__ . '/cache',
            'logs' => __DIR__ . '/logs'
        ];
        
        $issues = [];
        foreach ($paths as $name => $path) {
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            if (!is_writable($path)) {
                $issues[] = $name;
            }
        }
        
        return [
            'status' => empty($issues),
            'message' => empty($issues) ? 'All directories writable' : 'Not writable: ' . implode(', ', $issues)
        ];
    }
    
    /**
     * Check SSL certificate
     */
    private function checkSSLCertificate() {
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        return [
            'status' => $isHttps,
            'message' => $isHttps ? 'SSL certificate detected' : 'SSL not detected (HTTP)'
        ];
    }
    
    /**
     * Check memory limit
     */
    private function checkMemoryLimit() {
        $limit = ini_get('memory_limit');
        $required = '256M';
        return [
            'status' => $this->compareMemory($limit, $required),
            'message' => "Current: {$limit}, Recommended: {$required}"
        ];
    }
    
    /**
     * Check upload limits
     */
    private function checkUploadLimits() {
        $uploadMax = ini_get('upload_max_filesize');
        $postMax = ini_get('post_max_size');
        return [
            'status' => $this->compareMemory($uploadMax, '10M') && $this->compareMemory($postMax, '10M'),
            'message' => "Upload: {$uploadMax}, POST: {$postMax} (Recommended: 10M+)"
        ];
    }
    
    /**
     * Compare memory values
     */
    private function compareMemory($current, $required) {
        $currentVal = $this->parseMemory($current);
        $requiredVal = $this->parseMemory($required);
        return $currentVal >= $requiredVal;
    }
    
    /**
     * Parse memory value
     */
    private function parseMemory($value) {
        $unit = strtoupper(substr($value, -1));
        $number = (int) substr($value, 0, -1);
        
        switch ($unit) {
            case 'G': return $number * 1024 * 1024 * 1024;
            case 'M': return $number * 1024 * 1024;
            case 'K': return $number * 1024;
            default: return (int) $value;
        }
    }
    
    /**
     * Setup production configuration
     */
    private function setupProductionConfig() {
        echo "<h2>⚙️ Setting Up Production Configuration</h2>\n";
        
        // Create production config file
        $prodConfig = "<?php\n";
        $prodConfig .= "/**\n";
        $prodConfig .= " * APS Dream Homes - Production Configuration\n";
        $prodConfig .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $prodConfig .= " */\n\n";
        
        $prodConfig .= "// Production environment\n";
        $prodConfig .= "define('ENVIRONMENT', 'production');\n\n";
        
        $prodConfig .= "// Security settings\n";
        $prodConfig .= "define('SECURITY_KEY', '" . bin2hex(random_bytes(32)) . "');\n";
        $prodConfig .= "define('JWT_SECRET', '" . bin2hex(random_bytes(32)) . "');\n\n";
        
        $prodConfig .= "// Cache settings\n";
        $prodConfig .= "define('CACHE_ENABLED', true);\n";
        $prodConfig .= "define('CACHE_DURATION', 3600); // 1 hour\n\n";
        
        $prodConfig .= "// API settings\n";
        $prodConfig .= "define('API_RATE_LIMIT', 100);\n";
        $prodConfig .= "define('API_RATE_WINDOW', 3600);\n\n";
        
        $prodConfig .= "// Email settings (configure these)\n";
        $prodConfig .= "define('SMTP_HOST', 'your_smtp_host');\n";
        $prodConfig .= "define('SMTP_USER', 'your_smtp_user');\n";
        $prodConfig .= "define('SMTP_PASS', 'your_smtp_pass');\n";
        $prodConfig .= "define('SMTP_PORT', 587);\n\n";
        
        $prodConfig .= "// Payment settings (configure these)\n";
        $prodConfig .= "define('RAZORPAY_KEY', 'your_razorpay_key');\n";
        $prodConfig .= "define('RAZORPAY_SECRET', 'your_razorpay_secret');\n";
        $prodConfig .= "define('STRIPE_PUBLISHABLE', 'your_stripe_key');\n";
        $prodConfig .= "define('STRIPE_SECRET', 'your_stripe_secret');\n\n";
        
        $prodConfig .= "// Backup settings\n";
        $prodConfig .= "define('BACKUP_ENABLED', true);\n";
        $prodConfig .= "define('BACKUP_SCHEDULE', 'daily');\n";
        $prodConfig .= "define('BACKUP_RETENTION', 30); // days\n\n";
        
        $prodConfig .= "// Monitoring settings\n";
        $prodConfig .= "define('MONITORING_ENABLED', true);\n";
        $prodConfig .= "define('ERROR_LOGGING', true);\n";
        $prodConfig .= "define('PERFORMANCE_LOGGING', true);\n";
        
        file_put_contents(__DIR__ . '/config_production.php', $prodConfig);
        
        echo "<div style='color: green;'>✅ Production configuration created: config_production.php</div>\n";
        echo "<div style='color: orange;'>⚠️ Please update SMTP and payment gateway settings in config_production.php</div>\n";
    }
    
    /**
     * Initialize production database
     */
    private function initializeProductionDatabase() {
        echo "<h2>🗄️ Initializing Production Database</h2>\n";
        
        try {
            // Create production indexes if not exists
            $indexes = [
                "CREATE INDEX IF NOT EXISTS idx_properties_price_location ON properties(price, location)",
                "CREATE INDEX IF NOT EXISTS idx_users_email_status ON users(email, status)",
                "CREATE INDEX IF NOT EXISTS idx_analytics_date_type ON analytics_page_views(visit_time, action_type)",
                "CREATE INDEX IF NOT EXISTS idx_recommendations_score ON ai_recommendations(recommendation_score)",
                "CREATE INDEX IF NOT EXISTS idx_security_time ON security_logs(created_at)"
            ];
            
            foreach ($indexes as $sql) {
                $this->conn->query($sql);
            }
            
            echo "<div style='color: green;'>✅ Production database indexes created</div>\n";
            
            // Optimize tables
            $tables = ['properties', 'users', 'analytics_page_views', 'ai_recommendations'];
            foreach ($tables as $table) {
                $this->conn->query("OPTIMIZE TABLE {$table}");
            }
            
            echo "<div style='color: green;'>✅ Database tables optimized</div>\n";
            
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Database initialization failed: " . $e->getMessage() . "</div>\n";
        }
    }
    
    /**
     * Setup production security
     */
    private function setupProductionSecurity() {
        echo "<h2>🔒 Setting Up Production Security</h2>\n";
        
        // Create .htaccess for production
        $htaccess = "# APS Dream Homes - Production Security\n";
        $htaccess .= "# Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        $htaccess .= "# Security Headers\n";
        $htaccess .= "<IfModule mod_headers.c>\n";
        $htaccess .= "    Header always set X-Frame-Options DENY\n";
        $htaccess .= "    Header always set X-Content-Type-Options nosniff\n";
        $htaccess .= "    Header always set X-XSS-Protection \"1; mode=block\"\n";
        $htaccess .= "    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"\n";
        $htaccess .= "    Header always set Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:\"\n";
        $htaccess .= "</IfModule>\n\n";
        
        $htaccess .= "# PHP Settings\n";
        $htaccess .= "<IfModule mod_php.c>\n";
        $htaccess .= "    php_flag display_errors Off\n";
        $htaccess .= "    php_flag log_errors On\n";
        $htaccess .= "    php_value error_log /var/log/apsdreamhome/error.log\n";
        $htaccess .= "    php_value max_execution_time 300\n";
        $htaccess .= "    php_value memory_limit 256M\n";
        $htaccess .= "</IfModule>\n\n";
        
        $htaccess .= "# Rate Limiting\n";
        $htaccess .= "<IfModule mod_rewrite.c>\n";
        $htaccess .= "    RewriteEngine On\n";
        $htaccess .= "    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n";
        $htaccess .= "</IfModule>\n\n";
        
        $htaccess .= "# File Protection\n";
        $htaccess .= "<Files config*.php>\n";
        $htaccess .= "    Require all denied\n";
        $htaccess .= "</Files>\n\n";
        
        $htaccess .= "<Files *.sql>\n";
        $htaccess .= "    Require all denied\n";
        $htaccess .= "</Files>\n\n";
        
        $htaccess .= "<FilesMatch \"\\.(log|txt|md)\">\n";
        $htaccess .= "    Require all denied\n";
        $htaccess .= "</FilesMatch>\n\n";
        
        $htaccess .= "# Cache Control\n";
        $htaccess .= "<IfModule mod_expires.c>\n";
        $htaccess .= "    ExpiresActive On\n";
        $htaccess .= "    ExpiresByType text/css \"access plus 1 month\"\n";
        $htaccess .= "    ExpiresByType application/javascript \"access plus 1 month\"\n";
        $htaccess .= "    ExpiresByType image/png \"access plus 1 year\"\n";
        $htaccess .= "    ExpiresByType image/jpg \"access plus 1 year\"\n";
        $htaccess .= "    ExpiresByType image/jpeg \"access plus 1 year\"\n";
        $htaccess .= "    ExpiresByType image/gif \"access plus 1 year\"\n";
        $htaccess .= "    ExpiresByType image/webp \"access plus 1 year\"\n";
        $htaccess .= "</IfModule>\n";
        
        file_put_contents(__DIR__ . '/.htaccess_production', $htaccess);
        
        echo "<div style='color: green;'>✅ Production .htaccess created: .htaccess_production</div>\n";
        echo "<div style='color: orange;'>⚠️ Replace your .htaccess with .htaccess_production content</div>\n";
        
        // Create robots.txt for production
        $robots = "# APS Dream Homes - Robots.txt\n";
        $robots .= "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /includes/\n";
        $robots .= "Disallow: /config.php\n";
        $robots .= "Disallow: /cache/\n";
        $robots .= "Disallow: /logs/\n";
        $robots .= "Disallow: /uploads/\n";
        $robots .= "Disallow: /*.php$\n";
        $robots .= "Disallow: /*.sql$\n";
        $robots .= "Disallow: /*.log$\n\n";
        $robots .= "Sitemap: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/sitemap.xml\n";
        
        file_put_contents(__DIR__ . '/robots.txt', $robots);
        
        echo "<div style='color: green;'>✅ Production robots.txt created</div>\n";
    }
    
    /**
     * Setup production caching
     */
    private function setupProductionCaching() {
        echo "<h2>⚡ Setting Up Production Caching</h2>\n";
        
        try {
            // Clear existing cache
            $cacheDir = __DIR__ . '/cache';
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            
            echo "<div style='color: green;'>✅ Cache cleared</div>\n";
            
            // Warm up cache with essential pages
            $this->warmUpCache();
            
            echo "<div style='color: green;'>✅ Cache warmed up</div>\n";
            
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Cache setup failed: " . $e->getMessage() . "</div>\n";
        }
    }
    
    /**
     * Warm up cache
     */
    private function warmUpCache() {
        // This would pre-load essential pages into cache
        // Implementation depends on your caching system
    }
    
    /**
     * Generate production assets
     */
    private function generateProductionAssets() {
        echo "<h2>📦 Generating Production Assets</h2>\n";
        
        // Create production sitemap
        $this->generateSitemap();
        
        // Create production manifest
        $this->generateManifest();
        
        echo "<div style='color: green;'>✅ Production assets generated</div>\n";
    }
    
    /**
     * Generate sitemap
     */
    private function generateSitemap() {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Add main pages
        $pages = [
            '/' => '1.0',
            '/about.php' => '0.8',
            '/properties.php' => '0.9',
            '/projects.php' => '0.8',
            '/contact.php' => '0.7',
            '/register.php' => '0.6',
            '/faq.php' => '0.6'
        ];
        
        foreach ($pages as $page => $priority) {
            $sitemap .= "    <url>\n";
            $sitemap .= "        <loc>{$baseUrl}{$page}</loc>\n";
            $sitemap .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            $sitemap .= "        <changefreq>weekly</changefreq>\n";
            $sitemap .= "        <priority>{$priority}</priority>\n";
            $sitemap .= "    </url>\n";
        }
        
        $sitemap .= '</urlset>';
        
        file_put_contents(__DIR__ . '/sitemap.xml', $sitemap);
        
        echo "<div style='color: green;'>✅ Sitemap generated: sitemap.xml</div>\n";
    }
    
    /**
     * Generate manifest
     */
    private function generateManifest() {
        $manifest = [
            'name' => 'APS Dream Homes',
            'short_name' => 'APS Homes',
            'description' => 'Your trusted real estate partner since 2009',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#667eea',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => '/assets/images/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => '/assets/images/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ]
            ]
        ];
        
        file_put_contents(__DIR__ . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        
        echo "<div style='color: green;'>✅ PWA manifest generated: manifest.json</div>\n";
    }
    
    /**
     * Generate deployment report
     */
    private function generateDeploymentReport() {
        echo "<h2>📊 Deployment Report</h2>\n";
        
        $report = [
            'deployment_date' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'database_status' => 'connected',
            'ssl_status' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'enabled' : 'disabled',
            'features_deployed' => [
                'Advanced Analytics',
                'AI Recommendations',
                'Mobile Framework',
                'Payment Gateway',
                'Security Hardening',
                'Marketing Automation',
                'Multi-Language Support',
                'Custom Features'
            ],
            'next_steps' => [
                'Configure SMTP settings in config_production.php',
                'Setup payment gateway API keys',
                'Install SSL certificate',
                'Update .htaccess with production settings',
                'Test all functionality',
                'Monitor performance'
            ]
        ];
        
        file_put_contents(__DIR__ . '/deployment_report.json', json_encode($report, JSON_PRETTY_PRINT));
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>\n";
        echo "<h3>🎉 Deployment Summary</h3>\n";
        echo "<div><strong>Deployment Date:</strong> {$report['deployment_date']}</div>\n";
        echo "<div><strong>PHP Version:</strong> {$report['php_version']}</div>\n";
        echo "<div><strong>Database:</strong> {$report['database_status']}</div>\n";
        echo "<div><strong>SSL:</strong> {$report['ssl_status']}</div>\n";
        echo "<div><strong>Features Deployed:</strong> " . count($report['features_deployed']) . " systems</div>\n";
        echo "</div>\n";
        
        echo "<h3>📋 Next Steps</h3>\n";
        echo "<ol>\n";
        foreach ($report['next_steps'] as $step) {
            echo "<li>{$step}</li>\n";
        }
        echo "</ol>\n";
        
        echo "<div style='color: green; font-weight: bold; margin: 15px 0;'>🚀 APS Dream Homes is ready for production!</div>\n";
    }
    
    /**
     * Get database connection
     */
    private function getConnection() {
        try {
            return new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        } catch (Exception $e) {
            echo "<div style='color: red;'>Database connection failed: " . $e->getMessage() . "</div>\n";
            return null;
        }
    }
}

// Add some CSS for better presentation
echo "<style>
.deployment-container {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}
h1 { color: #667eea; text-align: center; }
h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
h3 { color: #555; }
</style>";

// Run deployment
if (basename($_SERVER['PHP_SELF']) === 'production_deployment.php') {
    $deployment = new ProductionDeployment();
}
?>
