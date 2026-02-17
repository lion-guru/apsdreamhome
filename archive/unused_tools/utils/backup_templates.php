<?php
/**
 * APS Dream Home - Template Backup System
 * Emergency backup for all header/footer templates
 */

class TemplateBackup {
    private $backupDir;
    private $manifest = [];
    
    public function __construct() {
        $this->backupDir = "backup/templates_" . date('Y-m-d_H-i-s');
    }
    
    /**
     * Create comprehensive template backup
     */
    public function createBackup() {
        echo "<h1>📦 APS Dream Home - Template Backup System</h1>\n";
        echo "<div class='backup-container'>\n";
        
        // Create backup directory
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
            echo "<div style='color: green;'>✅ Created backup directory: {$this->backupDir}</div>\n";
        }
        
        // Backup all template categories
        $this->backupStandardTemplates();
        $this->backupUnifiedTemplates();
        $this->backupEnhancedTemplates();
        $this->backupAdminTemplates();
        $this->backupRoleBasedTemplates();
        $this->backupLayoutTemplates();
        
        // Create backup manifest
        $this->createManifest();
        
        // Display summary
        $this->displaySummary();
        
        echo "</div>\n";
    }
    
    /**
     * Backup standard templates
     */
    private function backupStandardTemplates() {
        echo "<h2>📋 Backing Up Standard Templates</h2>\n";
        
        $templates = [
            'includes/components/header.php',
            'includes/templates/footer.php'
        ];
        
        $backupSubDir = $this->backupDir . '/standard/';
        mkdir($backupSubDir, 0755, true);
        
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $dest = $backupSubDir . basename($template);
                copy($template, $dest);
                $this->addToManifest($template, $dest);
                echo "<div style='color: green;'>✅ Backed up: {$template}</div>\n";
            } else {
                echo "<div style='color: orange;'>⚠️ Missing: {$template}</div>\n";
            }
        }
    }
    
    /**
     * Backup unified templates
     */
    private function backupUnifiedTemplates() {
        echo "<h2>🔄 Backing Up Unified Templates</h2>\n";
        
        $templates = [
            'includes/app_header.php',
            'includes/app_footer.php'
        ];
        
        $backupSubDir = $this->backupDir . '/unified/';
        mkdir($backupSubDir, 0755, true);
        
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $dest = $backupSubDir . basename($template);
                copy($template, $dest);
                $this->addToManifest($template, $dest);
                echo "<div style='color: green;'>✅ Backed up: {$template}</div>\n";
            } else {
                echo "<div style='color: orange;'>⚠️ Missing: {$template}</div>\n";
            }
        }
    }
    
    /**
     * Backup enhanced templates
     */
    private function backupEnhancedTemplates() {
        echo "<h2>⚡ Backing Up Enhanced Templates</h2>\n";
        
        $templates = [
            'includes/enhanced_universal_template.php'
        ];
        
        $backupSubDir = $this->backupDir . '/enhanced/';
        mkdir($backupSubDir, 0755, true);
        
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $dest = $backupSubDir . basename($template);
                copy($template, $dest);
                $this->addToManifest($template, $dest);
                echo "<div style='color: green;'>✅ Backed up: {$template}</div>\n";
            } else {
                echo "<div style='color: orange;'>⚠️ Missing: {$template}</div>\n";
            }
        }
    }
    
    /**
     * Backup admin templates
     */
    private function backupAdminTemplates() {
        echo "<h2>🛡️ Backing Up Admin Templates</h2>\n";
        
        $templates = [
            'admin/includes/admin_header.php',
            'admin/includes/modern-footer.php',
            'admin/includes/modern-header.php',
            'admin/includes/new_header.php',
            'admin/includes/new_footer.php'
        ];
        
        $backupSubDir = $this->backupDir . '/admin/';
        mkdir($backupSubDir, 0755, true);
        
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $dest = $backupSubDir . basename($template);
                copy($template, $dest);
                $this->addToManifest($template, $dest);
                echo "<div style='color: green;'>✅ Backed up: {$template}</div>\n";
            } else {
                echo "<div style='color: orange;'>⚠️ Missing: {$template}</div>\n";
            }
        }
    }
    
    /**
     * Backup role-based templates
     */
    private function backupRoleBasedTemplates() {
        echo "<h2>👥 Backing Up Role-Based Templates</h2>\n";
        
        $templates = [
            'app/views/layouts/associate_header.php',
            'app/views/layouts/customer_header.php',
            'app/views/layouts/employee_header.php',
            'app/views/layouts/associate_footer.php',
            'app/views/layouts/customer_footer.php',
            'app/views/layouts/employee_footer.php'
        ];
        
        $backupSubDir = $this->backupDir . '/role_based/';
        mkdir($backupSubDir, 0755, true);
        
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $dest = $backupSubDir . basename($template);
                copy($template, $dest);
                $this->addToManifest($template, $dest);
                echo "<div style='color: green;'>✅ Backed up: {$template}</div>\n";
            } else {
                echo "<div style='color: orange;'>⚠️ Missing: {$template}</div>\n";
            }
        }
    }
    
    /**
     * Backup layout templates
     */
    private function backupLayoutTemplates() {
        echo "<h2>🎨 Backing Up Layout Templates</h2>\n";
        
        $templates = [
            'app/views/layouts/header_unified.php',
            'app/views/layouts/footer_unified.php',
            'app/views/layouts/header_new.php',
            'app/views/layouts/footer.php',
            'app/views/layouts/modern_header.php',
            'app/views/layouts/modern_footer.php'
        ];
        
        $backupSubDir = $this->backupDir . '/layouts/';
        mkdir($backupSubDir, 0755, true);
        
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $dest = $backupSubDir . basename($template);
                copy($template, $dest);
                $this->addToManifest($template, $dest);
                echo "<div style='color: green;'>✅ Backed up: {$template}</div>\n";
            } else {
                echo "<div style='color: orange;'>⚠️ Missing: {$template}</div>\n";
            }
        }
    }
    
    /**
     * Add file to manifest
     */
    private function addToManifest($original, $backup) {
        $this->manifest[] = [
            'original' => $original,
            'backup' => $backup,
            'size' => file_exists($backup) ? filesize($backup) : 0,
            'date' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Create backup manifest
     */
    private function createManifest() {
        echo "<h2>📋 Creating Backup Manifest</h2>\n";
        
        $manifestData = [
            'backup_info' => [
                'backup_date' => date('Y-m-d H:i:s'),
                'backup_directory' => $this->backupDir,
                'total_files' => count($this->manifest),
                'total_size' => array_sum(array_column($this->manifest, 'size'))
            ],
            'files' => $this->manifest
        ];
        
        $manifestFile = $this->backupDir . '/manifest.json';
        file_put_contents($manifestFile, json_encode($manifestData, JSON_PRETTY_PRINT));
        
        echo "<div style='color: green;'>✅ Manifest created: {$manifestFile}</div>\n";
    }
    
    /**
     * Display backup summary
     */
    private function displaySummary() {
        echo "<h2>📊 Backup Summary</h2>\n";
        
        $totalFiles = count($this->manifest);
        $totalSize = array_sum(array_column($this->manifest, 'size'));
        $totalSizeMB = round($totalSize / 1024 / 1024, 2);
        
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>\n";
        echo "<h3>✅ Template Backup Complete!</h3>\n";
        echo "<p><strong>Backup Directory:</strong> {$this->backupDir}</p>\n";
        echo "<p><strong>Total Files:</strong> {$totalFiles}</p>\n";
        echo "<p><strong>Total Size:</strong> {$totalSizeMB} MB</p>\n";
        echo "<p><strong>Backup Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        echo "<p><strong>Manifest File:</strong> {$this->backupDir}/manifest.json</p>\n";
        echo "<p><strong>Next Steps:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Verify backup integrity by checking files</li>\n";
        echo "<li>Proceed with template consolidation</li>\n";
        echo "<li>Archive old templates after consolidation</li>\n";
        echo "<li>Test new template system</li>\n";
        echo "</ul>\n";
        echo "</div>\n";
    }
}

// Run backup if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $backup = new TemplateBackup();
        $backup->createBackup();
    } catch (Exception $e) {
        echo "<h1>❌ Backup Error</h1>\n";
        echo "<p>Error: " . $e->getMessage() . "</p>\n";
    }
}
?>

