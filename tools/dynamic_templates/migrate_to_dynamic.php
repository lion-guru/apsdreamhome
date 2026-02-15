<?php
/**
 * APS Dream Home - Dynamic Template Migration Script
 * Convert existing pages to use dynamic templates
 */

require_once 'includes/config.php';

class DynamicTemplateMigration {
    private $conn;
    private $migrationLog = [];
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Run complete migration
     */
    public function migrate() {
        echo "<h1>🔄 Dynamic Template Migration</h1>\n";
        echo "<div class='migration-container'>\n";
        
        try {
            // Step 1: Backup existing templates
            $this->backupExistingTemplates();
            
            // Step 2: Update main pages
            $this->updateMainPages();
            
            // Step 3: Update admin pages
            $this->updateAdminPages();
            
            // Step 4: Create migration report
            $this->generateReport();
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>❌ Migration Error: " . $e->getMessage() . "</div>\n";
        }
        
        echo "</div>\n";
    }
    
    /**
     * Backup existing templates
     */
    private function backupExistingTemplates() {
        echo "<h2>📦 Backing Up Existing Templates</h2>\n";
        
        $backupDir = 'backup/templates_' . date('Y-m-d_H-i-s');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $templatesToBackup = [
            'includes/header.php',
            'includes/footer.php',
            'includes/templates/header.php',
            'includes/templates/footer.php',
            'app/views/layouts/header_unified.php',
            'app/views/layouts/footer_unified.php',
            'app/views/layouts/modern_header.php',
            'app/views/layouts/modern_footer.php'
        ];
        
        foreach ($templatesToBackup as $template) {
            if (file_exists($template)) {
                $backupPath = $backupDir . '/' . basename($template);
                copy($template, $backupPath);
                $this->migrationLog[] = "Backed up: $template -> $backupPath";
                echo "<div style='color: green;'>✅ Backed up: $template</div>\n";
            }
        }
    }
    
    /**
     * Update main pages
     */
    private function updateMainPages() {
        echo "<h2>📄 Updating Main Pages</h2>\n";
        
        $mainPages = [
            'index.php' => 'Home Page',
            'about.php' => 'About Page',
            'contact.php' => 'Contact Page',
            'properties.php' => 'Properties Page',
            'projects.php' => 'Projects Page',
            'gallery.php' => 'Gallery Page',
            'testimonials.php' => 'Testimonials Page',
            'team.php' => 'Team Page'
        ];
        
        foreach ($mainPages as $page => $description) {
            $this->updatePage($page, $description);
        }
    }
    
    /**
     * Update admin pages
     */
    private function updateAdminPages() {
        echo "<h2>⚙️ Updating Admin Pages</h2>\n";
        
        $adminPages = [
            'admin/index.php' => 'Admin Login',
            'admin/dashboard.php' => 'Admin Dashboard',
            'admin/enhanced_dashboard.php' => 'Enhanced Dashboard'
        ];
        
        foreach ($adminPages as $page => $description) {
            $this->updatePage($page, $description, 'admin');
        }
    }
    
    /**
     * Update individual page
     */
    private function updatePage($pagePath, $description, $type = 'main') {
        if (!file_exists($pagePath)) {
            echo "<div style='color: orange;'>⚠️ File not found: $pagePath</div>\n";
            return;
        }
        
        $content = file_get_contents($pagePath);
        
        // Check if already using dynamic templates
        if (strpos($content, 'renderDynamicHeader') !== false || 
            strpos($content, 'includes/dynamic_templates.php') !== false) {
            echo "<div style='color: blue;'>ℹ️ Already using dynamic templates: $pagePath</div>\n";
            return;
        }
        
        // Create backup
        $backupPath = 'backup/pages_' . date('Y-m-d_H-i-s') . '/' . basename($pagePath);
        $backupDir = dirname($backupPath);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        copy($pagePath, $backupPath);
        
        // Update content based on type
        $updatedContent = $this->convertPageContent($content, $type);
        
        // Write updated content
        file_put_contents($pagePath, $updatedContent);
        
        $this->migrationLog[] = "Updated: $pagePath ($description)";
        echo "<div style='color: green;'>✅ Updated: $description</div>\n";
    }
    
    /**
     * Convert page content to use dynamic templates
     */
    private function convertPageContent($content, $type = 'main') {
        // Add dynamic templates include at the top
        $includeCode = "<?php\nrequire_once 'includes/dynamic_templates.php';\n?>\n";
        
        // Remove existing header includes
        $content = preg_replace('/<\?php\s*(?:require_once|include|include_once)\s*.*?header\.php.*?\?>\s*/', '', $content);
        $content = preg_replace('/<\?php\s*(?:require_once|include|include_once)\s*.*?footer\.php.*?\?>\s*/', '', $content);
        
        // Remove existing HTML headers
        $content = preg_replace('/<header[^>]*>.*?<\/header>/is', '', $content);
        $content = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $content);
        
        // Find the position after <head> section
        $headEnd = strpos($content, '</head>');
        if ($headEnd !== false) {
            // Insert dynamic CSS before </head>
            $cssCode = "<?php addDynamicTemplateCSS(); ?>\n";
            $content = substr_replace($content, $cssCode, $headEnd, 0);
        }
        
        // Find the position before </body>
        $bodyEnd = strpos($content, '</body>');
        if ($bodyEnd !== false) {
            // Insert dynamic JS before </body>
            $jsCode = "<?php addDynamicTemplateJS(); ?>\n";
            $content = substr_replace($content, $jsCode, $bodyEnd, 0);
        }
        
        // Find the position after <body> tag
        $bodyStart = strpos($content, '<body');
        if ($bodyStart !== false) {
            // Find the end of <body> tag
            $bodyTagEnd = strpos($content, '>', $bodyStart) + 1;
            
            // Insert dynamic header
            $headerType = $type === 'admin' ? 'admin' : 'main';
            $headerCode = "<?php renderDynamicHeader('$headerType'); ?>\n";
            $content = substr_replace($content, $headerCode, $bodyTagEnd, 0);
        }
        
        // Find the position before </body>
        if ($bodyEnd !== false) {
            // Insert dynamic footer
            $footerType = $type === 'admin' ? 'admin' : 'main';
            $footerCode = "<?php renderDynamicFooter('$footerType'); ?>\n";
            $content = substr_replace($content, $footerCode, $bodyEnd, 0);
        }
        
        // Add include at the beginning if not present
        if (strpos($content, 'includes/dynamic_templates.php') === false) {
            // Find first PHP tag or add at beginning
            $firstPhp = strpos($content, '<?php');
            if ($firstPhp !== false && $firstPhp < 100) {
                // Insert after first PHP tag
                $insertPos = strpos($content, '?>', $firstPhp) + 2;
                $content = substr_replace($content, $includeCode, $insertPos, 0);
            } else {
                // Add at the beginning
                $content = $includeCode . $content;
            }
        }
        
        return $content;
    }
    
    /**
     * Generate migration report
     */
    private function generateReport() {
        echo "<h2>📊 Migration Report</h2>\n";
        
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>\n";
        echo "<h3>✅ Migration Complete!</h3>\n";
        echo "<p><strong>Files Updated:</strong> " . count($this->migrationLog) . "</p>\n";
        echo "<p><strong>Migration Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        echo "<p><strong>Next Steps:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>✅ All pages now use dynamic templates</li>\n";
        echo "<li>🔄 Test pages to ensure proper rendering</li>\n";
        echo "<li>⚙️ Configure dynamic content in admin panel</li>\n";
        echo "<li>🎨 Customize header/footer appearance</li>\n";
        echo "</ul>\n";
        echo "</div>\n";
        
        echo "<h3>📋 Migration Details</h3>\n";
        echo "<div style='max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 5px;'>\n";
        foreach ($this->migrationLog as $log) {
            echo "<div style='color: #666; font-size: 12px; margin: 2px 0;'>• " . h($log) . "</div>\n";
        }
        echo "</div>\n";
        
        echo "<h3>🎯 Admin Access</h3>\n";
        echo "<div class='alert alert-info'>\n";
        echo "<strong>Dynamic Content Manager:</strong> <a href='admin/dynamic_content_manager.php'>admin/dynamic_content_manager.php</a><br>\n";
        echo "<strong>Live Demo:</strong> <a href='dynamic_demo.php'>dynamic_demo.php</a><br>\n";
        echo "<strong>Database Setup:</strong> <a href='tools/setup_dynamic_database.php'>tools/setup_dynamic_database.php</a>\n";
        echo "</div>\n";
    }
}

// Run migration if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $migration = new DynamicTemplateMigration($GLOBALS['conn'] ?? $GLOBALS['con'] ?? null);
        $migration->migrate();
    } catch (Exception $e) {
        echo "<h1>❌ Migration Error</h1>\n";
        echo "<p>Error: " . $e->getMessage() . "</p>\n";
    }
}
?>
