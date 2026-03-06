<?php
/**
 * APS Dream Home - Project Organizer
 * Cleans up duplicates and organizes project structure
 */

class ProjectOrganizer {
    private $root;
    private $actions = [];
    
    public function __construct($root = null) {
        $this->root = $root ?: __DIR__;
    }
    
    public function organize() {
        echo "🧹 Starting Project Organization...\n";
        
        // 1. Handle duplicates
        $this->handleDuplicates();
        
        // 2. Clean up deprecated files
        $this->cleanupDeprecated();
        
        // 3. Organize layouts
        $this->organizeLayouts();
        
        // 4. Create unified structure
        $this->createUnifiedStructure();
        
        // 5. Generate report
        $this->generateReport();
        
        echo "✅ Organization Complete!\n";
    }
    
    private function handleDuplicates() {
        echo "\n📋 Handling duplicates...\n";
        
        // Handle index.php duplicates
        $routesIndex = $this->root . '/routes/index.php';
        $publicIndex = $this->root . '/public/index.php';
        
        if (file_exists($routesIndex) && file_exists($publicIndex)) {
            echo "  📄 Found duplicate index.php files\n";
            echo "    - Keeping: public/index.php (main entry point)\n";
            echo "    - Renaming: routes/index.php → routes/router.php\n";
            
            if (rename($routesIndex, $this->root . '/routes/router.php')) {
                $this->actions[] = "Renamed routes/index.php to routes/router.php";
                
                // Update any references
                $this->updateReferences('routes/index.php', 'routes/router.php');
            }
        }
    }
    
    private function cleanupDeprecated() {
        echo "\n🗑️ Cleaning up deprecated files...\n";
        
        $deprecatedDir = $this->root . '/_DEPRECATED';
        if (is_dir($deprecatedDir)) {
            $files = scandir($deprecatedDir);
            $count = 0;
            
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') continue;
                
                $filePath = $deprecatedDir . '/' . $file;
                if (is_file($filePath)) {
                    unlink($filePath);
                    $count++;
                }
            }
            
            echo "  🗑️ Removed {$count} deprecated files\n";
            $this->actions[] = "Cleaned up {$count} deprecated files";
        }
    }
    
    private function organizeLayouts() {
        echo "\n🎨 Organizing layouts...\n";
        
        $layoutsDir = $this->root . '/app/views/layouts';
        if (is_dir($layoutsDir)) {
            $layouts = scandir($layoutsDir);
            $organized = [];
            
            foreach ($layouts as $layout) {
                if ($layout == '.' || $layout == '..') continue;
                
                $layoutPath = $layoutsDir . '/' . $layout;
                if (is_file($layoutPath)) {
                    // Keep only essential layouts
                    if (in_array($layout, ['base_new.php', 'header_new.php', 'footer_new.php'])) {
                        $organized[] = $layout;
                    } else {
                        // Move old layouts to backup
                        $backupDir = $this->root . '/_BACKUP/layouts';
                        if (!is_dir($backupDir)) {
                            mkdir($backupDir, 0755, true);
                        }
                        
                        if (rename($layoutPath, $backupDir . '/' . $layout)) {
                            echo "    📦 Moved: {$layout} → _BACKUP/layouts/\n";
                            $this->actions[] = "Moved old layout: {$layout}";
                        }
                    }
                }
            }
            
            echo "  📁 Kept " . count($organized) . " essential layouts\n";
        }
    }
    
    private function createUnifiedStructure() {
        echo "\n🏗️ Creating unified structure...\n";
        
        // Create unified config
        $unifiedConfig = [
            'app' => [
                'name' => 'APS Dream Home',
                'version' => '2.0.0',
                'environment' => $_ENV['APP_ENV'] ?? 'development',
                'timezone' => 'Asia/Kolkata',
                'base_url' => $_ENV['BASE_URL'] ?? 'http://localhost/apsdreamhome/public'
            ],
            'database' => [
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'database' => $_ENV['DB_DATABASE'] ?? 'apsdreamhome',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? ''
            ],
            'paths' => [
                'root' => $this->root,
                'app' => $this->root . '/app',
                'public' => $this->root . '/public',
                'storage' => $this->root . '/storage',
                'assets' => $this->root . '/public/assets'
            ]
        ];
        
        $configPath = $this->root . '/config/unified.php';
        file_put_contents($configPath, '<?php return ' . var_export($unifiedConfig, true) . ';');
        echo "  📝 Created unified config: config/unified.php\n";
        $this->actions[] = "Created unified configuration";
        
        // Create structure map
        $structure = [
            'controllers' => 'app/Http/Controllers',
            'models' => 'app/Models', 
            'views' => 'app/views',
            'layouts' => 'app/views/layouts',
            'routes' => 'routes',
            'public' => 'public',
            'assets' => 'public/assets',
            'config' => 'config',
            'storage' => 'storage'
        ];
        
        $mapPath = $this->root . '/project_structure.json';
        file_put_contents($mapPath, json_encode($structure, JSON_PRETTY_PRINT));
        echo "  🗺️ Created structure map: project_structure.json\n";
        $this->actions[] = "Created project structure map";
    }
    
    private function updateReferences($old, $new) {
        // Update references in key files
        $files = [
            'public/index.php',
            'routes/web.php'
        ];
        
        foreach ($files as $file) {
            $filePath = $this->root . '/' . $file;
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $content = str_replace($old, $new, $content);
                file_put_contents($filePath, $content);
                echo "    🔄 Updated references in {$file}\n";
            }
        }
    }
    
    private function generateReport() {
        echo "\n📄 Organization Report:\n";
        echo "Actions performed:\n";
        
        foreach ($this->actions as $i => $action) {
            echo "  " . ($i + 1) . ". {$action}\n";
        }
        
        // Save detailed report
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'actions' => $this->actions,
            'structure' => $this->getProjectStructure()
        ];
        
        $reportPath = $this->root . '/organization_report_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "\n📊 Detailed report saved: " . basename($reportPath) . "\n";
    }
    
    private function getProjectStructure() {
        $structure = [];
        $dirs = ['app', 'public', 'routes', 'config', 'storage'];
        
        foreach ($dirs as $dir) {
            $structure[$dir] = $this->scanDirectoryStructure($dir);
        }
        
        return $structure;
    }
    
    private function scanDirectoryStructure($dir) {
        $path = $this->root . '/' . $dir;
        if (!is_dir($path)) return [];
        
        $result = [];
        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            
            $itemPath = $path . '/' . $item;
            $result[$item] = is_dir($itemPath) ? 'directory' : 'file';
        }
        
        return $result;
    }
}

// Run organizer
$organizer = new ProjectOrganizer();
$organizer->organize();
?>
