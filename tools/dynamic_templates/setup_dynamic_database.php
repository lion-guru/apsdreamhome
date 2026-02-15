<?php
/**
 * APS Dream Home - Dynamic Database Setup
 * Create tables for dynamic header/footer and content management
 */

require_once 'includes/config.php';

class DynamicDatabaseSetup {
    private $conn;
    
    public function __construct() {
        $this->conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
        if (!$this->conn) {
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Setup all dynamic database tables
     */
    public function setup() {
        echo "<h1>🗄️ APS Dream Home - Dynamic Database Setup</h1>\n";
        echo "<div class='setup-container'>\n";
        
        try {
            // Create dynamic headers table
            $this->createDynamicHeadersTable();
            
            // Create dynamic footers table
            $this->createDynamicFootersTable();
            
            // Create site content table
            $this->createSiteContentTable();
            
            // Create media library table
            $this->createMediaLibraryTable();
            
            // Create page templates table
            $this->createPageTemplatesTable();
            
            // Insert default data
            $this->insertDefaultData();
            
            // Display summary
            $this->displaySummary();
            
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>\n";
        }
        
        echo "</div>\n";
    }
    
    /**
     * Create dynamic headers table
     */
    private function createDynamicHeadersTable() {
        echo "<h2>📋 Creating Dynamic Headers Table</h2>\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS `dynamic_headers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `header_type` enum('main','admin','user','mobile') NOT NULL DEFAULT 'main',
            `title` varchar(255) NOT NULL DEFAULT 'APS Dream Homes',
            `subtitle` text DEFAULT NULL,
            `logo_url` varchar(500) DEFAULT NULL,
            `logo_alt` varchar(255) DEFAULT NULL,
            `background_color` varchar(50) DEFAULT '#ffffff',
            `text_color` varchar(50) DEFAULT '#333333',
            `menu_items` json DEFAULT NULL,
            `custom_css` text DEFAULT NULL,
            `custom_js` text DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `created_by` int(11) DEFAULT NULL,
            `updated_by` int(11) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_header_type` (`header_type`),
            KEY `idx_active` (`is_active`),
            KEY `idx_created_by` (`created_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($this->conn->query($sql)) {
            echo "<div style='color: green;'>✅ Dynamic headers table created</div>\n";
        } else {
            throw new Exception("Failed to create dynamic_headers table: " . $this->conn->error);
        }
    }
    
    /**
     * Create dynamic footers table
     */
    private function createDynamicFootersTable() {
        echo "<h2>📋 Creating Dynamic Footers Table</h2>\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS `dynamic_footers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `footer_type` enum('main','admin','user','mobile') NOT NULL DEFAULT 'main',
            `company_info` json DEFAULT NULL,
            `quick_links` json DEFAULT NULL,
            `services` json DEFAULT NULL,
            `contact_info` json DEFAULT NULL,
            `social_links` json DEFAULT NULL,
            `copyright_text` text DEFAULT NULL,
            `custom_css` text DEFAULT NULL,
            `custom_js` text DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `created_by` int(11) DEFAULT NULL,
            `updated_by` int(11) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_footer_type` (`footer_type`),
            KEY `idx_active` (`is_active`),
            KEY `idx_created_by` (`created_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($this->conn->query($sql)) {
            echo "<div style='color: green;'>✅ Dynamic footers table created</div>\n";
        } else {
            throw new Exception("Failed to create dynamic_footers table: " . $this->conn->error);
        }
    }
    
    /**
     * Create site content table
     */
    private function createSiteContentTable() {
        echo "<h2>📋 Creating Site Content Table</h2>\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS `site_content` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `content_type` enum('page','section','component','meta') NOT NULL,
            `content_key` varchar(255) NOT NULL,
            `title` varchar(255) DEFAULT NULL,
            `content` longtext DEFAULT NULL,
            `meta_title` varchar(255) DEFAULT NULL,
            `meta_description` text DEFAULT NULL,
            `meta_keywords` text DEFAULT NULL,
            `status` enum('draft','published','archived') DEFAULT 'draft',
            `template_id` int(11) DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `updated_by` int(11) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_content` (`content_type`, `content_key`),
            KEY `idx_status` (`status`),
            KEY `idx_content_type` (`content_type`),
            KEY `idx_template_id` (`template_id`),
            KEY `idx_created_by` (`created_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($this->conn->query($sql)) {
            echo "<div style='color: green;'>✅ Site content table created</div>\n";
        } else {
            throw new Exception("Failed to create site_content table: " . $this->conn->error);
        }
    }
    
    /**
     * Create media library table
     */
    private function createMediaLibraryTable() {
        echo "<h2>📋 Creating Media Library Table</h2>\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS `media_library` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `media_type` enum('image','video','document','icon') NOT NULL,
            `file_name` varchar(255) NOT NULL,
            `original_name` varchar(255) DEFAULT NULL,
            `file_path` varchar(500) NOT NULL,
            `file_size` int(11) DEFAULT NULL,
            `mime_type` varchar(100) DEFAULT NULL,
            `dimensions` varchar(50) DEFAULT NULL,
            `alt_text` varchar(255) DEFAULT NULL,
            `caption` text DEFAULT NULL,
            `tags` json DEFAULT NULL,
            `usage_count` int(11) DEFAULT 0,
            `uploaded_by` int(11) DEFAULT NULL,
            `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_media_type` (`media_type`),
            KEY `idx_uploaded_by` (`uploaded_by`),
            FULLTEXT KEY `ft_search` (`file_name`, `alt_text`, `caption`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($this->conn->query($sql)) {
            echo "<div style='color: green;'>✅ Media library table created</div>\n";
        } else {
            throw new Exception("Failed to create media_library table: " . $this->conn->error);
        }
    }
    
    /**
     * Create page templates table
     */
    private function createPageTemplatesTable() {
        echo "<h2>📋 Creating Page Templates Table</h2>\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS `page_templates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `template_name` varchar(100) NOT NULL,
            `template_type` enum('header','footer','page','section') NOT NULL,
            `template_code` longtext NOT NULL,
            `variables` json DEFAULT NULL,
            `preview_image` varchar(500) DEFAULT NULL,
            `is_default` tinyint(1) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            `created_by` int(11) DEFAULT NULL,
            `updated_by` int(11) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_template` (`template_name`, `template_type`),
            KEY `idx_template_type` (`template_type`),
            KEY `idx_active` (`is_active`),
            KEY `idx_default` (`is_default`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($this->conn->query($sql)) {
            echo "<div style='color: green;'>✅ Page templates table created</div>\n";
        } else {
            throw new Exception("Failed to create page_templates table: " . $this->conn->error);
        }
    }
    
    /**
     * Insert default data
     */
    private function insertDefaultData() {
        echo "<h2>📝 Inserting Default Data</h2>\n";
        
        // Default header data
        $defaultHeader = [
            'header_type' => 'main',
            'title' => 'APS Dream Homes',
            'subtitle' => 'Premium Real Estate Solutions',
            'logo_url' => '/assets/images/logo.png',
            'logo_alt' => 'APS Dream Homes Logo',
            'background_color' => '#ffffff',
            'text_color' => '#333333',
            'menu_items' => json_encode([
                ['label' => 'Home', 'url' => '/', 'icon' => 'fas fa-home'],
                ['label' => 'Properties', 'url' => '/properties.php', 'icon' => 'fas fa-building'],
                ['label' => 'Projects', 'url' => '/projects.php', 'icon' => 'fas fa-project-diagram'],
                ['label' => 'About', 'url' => '/about.php', 'icon' => 'fas fa-info-circle'],
                ['label' => 'Contact', 'url' => '/contact.php', 'icon' => 'fas fa-envelope']
            ]),
            'is_active' => 1,
            'created_by' => 1
        ];
        
        $sql = "INSERT INTO dynamic_headers (header_type, title, subtitle, logo_url, logo_alt, background_color, text_color, menu_items, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssssssssi', 
            $defaultHeader['header_type'],
            $defaultHeader['title'],
            $defaultHeader['subtitle'],
            $defaultHeader['logo_url'],
            $defaultHeader['logo_alt'],
            $defaultHeader['background_color'],
            $defaultHeader['text_color'],
            $defaultHeader['menu_items'],
            $defaultHeader['is_active'],
            $defaultHeader['created_by']
        );
        
        if ($stmt->execute()) {
            echo "<div style='color: green;'>✅ Default header data inserted</div>\n";
        } else {
            echo "<div style='color: orange;'>⚠️ Default header data already exists</div>\n";
        }
        
        // Default footer data
        $defaultFooter = [
            'footer_type' => 'main',
            'company_info' => json_encode([
                'name' => 'APS Dream Homes',
                'description' => 'Premium real estate properties across Uttar Pradesh. Find your dream home with our expert guidance.',
                'address' => 'Kunraghat, Gorakhpur, UP - 273008',
                'phone' => '+91-9554000001',
                'email' => 'info@apsdreamhomes.com'
            ]),
            'quick_links' => json_encode([
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Properties', 'url' => '/properties.php'],
                ['label' => 'Projects', 'url' => '/projects.php'],
                ['label' => 'About Us', 'url' => '/about.php'],
                ['label' => 'Contact', 'url' => '/contact.php']
            ]),
            'services' => json_encode([
                ['label' => 'Property Buying', 'url' => '/services.php'],
                ['label' => 'Property Selling', 'url' => '/services.php'],
                ['label' => 'Investment Advisory', 'url' => '/services.php'],
                ['label' => 'Legal Assistance', 'url' => '/services.php'],
                ['label' => 'Property Management', 'url' => '/services.php']
            ]),
            'social_links' => json_encode([
                ['platform' => 'facebook', 'url' => '#', 'icon' => 'fab fa-facebook-f'],
                ['platform' => 'twitter', 'url' => '#', 'icon' => 'fab fa-twitter'],
                ['platform' => 'instagram', 'url' => '#', 'icon' => 'fab fa-instagram'],
                ['platform' => 'linkedin', 'url' => '#', 'icon' => 'fab fa-linkedin-in']
            ]),
            'copyright_text' => '© ' . date('Y') . ' APS Dream Homes. All rights reserved.',
            'is_active' => 1,
            'created_by' => 1
        ];
        
        $sql = "INSERT INTO dynamic_footers (footer_type, company_info, quick_links, services, social_links, copyright_text, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('sssssssi', 
            $defaultFooter['footer_type'],
            $defaultFooter['company_info'],
            $defaultFooter['quick_links'],
            $defaultFooter['services'],
            $defaultFooter['social_links'],
            $defaultFooter['copyright_text'],
            $defaultFooter['is_active'],
            $defaultFooter['created_by']
        );
        
        if ($stmt->execute()) {
            echo "<div style='color: green;'>✅ Default footer data inserted</div>\n";
        } else {
            echo "<div style='color: orange;'>⚠️ Default footer data already exists</div>\n";
        }
        
        // Default site content
        $defaultContent = [
            ['content_type' => 'page', 'content_key' => 'home', 'title' => 'Home', 'content' => 'Welcome to APS Dream Homes', 'status' => 'published'],
            ['content_type' => 'page', 'content_key' => 'about', 'title' => 'About Us', 'content' => 'About APS Dream Homes', 'status' => 'published'],
            ['content_type' => 'page', 'content_key' => 'contact', 'title' => 'Contact Us', 'content' => 'Contact APS Dream Homes', 'status' => 'published'],
            ['content_type' => 'meta', 'content_key' => 'site_title', 'title' => 'Site Title', 'content' => 'APS Dream Homes | Premium Real Estate', 'status' => 'published'],
            ['content_type' => 'meta', 'content_key' => 'site_description', 'title' => 'Site Description', 'content' => 'APS Dream Homes - Premium real estate properties in Gorakhpur, Lucknow, Kanpur', 'status' => 'published']
        ];
        
        foreach ($defaultContent as $content) {
            $contentType = $content['content_type'];
            $contentKey = $content['content_key'];
            $title = $content['title'];
            $contentText = $content['content'];
            $status = $content['status'];
            $createdBy = 1;
            
            $sql = "INSERT IGNORE INTO site_content (content_type, content_key, title, content, status, created_by) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('sssssi', $contentType, $contentKey, $title, $contentText, $status, $createdBy);
            $stmt->execute();
        }
        
        echo "<div style='color: green;'>✅ Default site content inserted</div>\n";
    }
    
    /**
     * Display setup summary
     */
    private function displaySummary() {
        echo "<h2>📊 Database Setup Summary</h2>\n";
        
        // Count tables
        $tables = ['dynamic_headers', 'dynamic_footers', 'site_content', 'media_library', 'page_templates'];
        $tableCount = 0;
        
        foreach ($tables as $table) {
            $result = $this->conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                $tableCount++;
            }
        }
        
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>\n";
        echo "<h3>✅ Dynamic Database Setup Complete!</h3>\n";
        echo "<p><strong>Tables Created:</strong> {$tableCount}/5</p>\n";
        echo "<p><strong>Default Data:</strong> Inserted</p>\n";
        echo "<p><strong>Setup Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        echo "<p><strong>Next Steps:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>✅ Database tables are ready for dynamic content</li>\n";
        echo "<li>🔄 Next: Create dynamic header/footer templates</li>\n";
        echo "<li>⚙️ Next: Build admin management interface</li>\n";
        echo "<li>🎨 Next: Implement visual content editor</li>\n";
        echo "<li>📱 Next: Add responsive mobile templates</li>\n";
        echo "</ul>\n";
        echo "</div>\n";
        
        // Table statistics
        echo "<h3>📋 Table Statistics</h3>\n";
        foreach ($tables as $table) {
            $result = $this->conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $result->fetch_assoc()['count'];
            echo "<div style='color: blue;'>📊 {$table}: {$count} records</div>\n";
        }
    }
}

// Run setup if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $setup = new DynamicDatabaseSetup();
        $setup->setup();
    } catch (Exception $e) {
        echo "<h1>❌ Setup Error</h1>\n";
        echo "<p>Error: " . $e->getMessage() . "</p>\n";
    }
}
?>
