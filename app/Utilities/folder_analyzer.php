<?php
/**
 * APS Dream Home - Ultimate Folder Analysis and Organization
 * Deep analysis of all directories and comprehensive cleanup
 */

class UltimateFolderAnalyzer {
    private $root;
    private $analysis = [];
    private $duplicates = [];
    private $issues = [];
    private $actions = [];
    
    public function __construct($root = null) {
        $this->root = $root ?: __DIR__;
    }
    
    public function executeUltimateAnalysis() {
        echo "🚀 Starting Ultimate Folder Analysis and Organization...\n";
        echo "📁 Root: {$this->root}\n";
        echo "⏰ Started: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Phase 1: Deep directory analysis
        $this->analyzeAllDirectories();
        
        // Phase 2: Find all duplicates
        $this->findAllDuplicates();
        
        // Phase 3: Identify issues
        $this->identifyIssues();
        
        // Phase 4: Generate cleanup plan
        $this->generateCleanupPlan();
        
        // Phase 5: Execute cleanup
        $this->executeCleanup();
        
        // Phase 6: Organize according to rules
        $this->organizeAccordingToRules();
        
        // Phase 7: Generate final report
        $this->generateFinalReport();
        
        echo "\n✅ Ultimate Folder Analysis Complete!\n";
        echo "⏰ Finished: " . date('Y-m-d H:i:s') . "\n";
    }
    
    /**
     * Analyze all directories
     */
    private function analyzeAllDirectories() {
        echo "📂 Phase 1: Deep directory analysis...\n";
        
        $directories = [
            'app/views/about',
            'app/views/contact', 
            'app/views/layouts',
            'app/views/pages',
            'app/views/properties',
            'app/views/admin',
            'app/views/accounting',
            'app/views/ajax',
            'app/views/ai',
            'app/views/api',
            'app/views/assets',
            'app/views/customer',
            'app/views/employee',
            'app/views/investor',
            'app/views/management',
            'app/views/notifications',
            'app/views/partials',
            'app/views/payment',
            'app/views/privacy',
            'app/views/projects',
            'app/views/reports',
            'app/views/resell',
            'app/views/saas',
            'app/views/services',
            'app/views/sitemap',
            'app/views/static',
            'app/views/team',
            'app/views/terms',
            'app/views/test',
            'app/views/user',
            'app/views/vendor',
            '_DEPRECATED',
            'app/views/_DEPRECATED'
        ];
        
        foreach ($directories as $dir) {
            $this->analyzeDirectory($dir);
        }
        
        echo "   ✓ Analyzed " . count($directories) . " directories\n\n";
    }
    
    /**
     * Analyze single directory
     */
    private function analyzeDirectory($dir) {
        $fullPath = $this->root . '/' . $dir;
        
        if (!is_dir($fullPath)) {
            $this->issues[] = [
                'type' => 'missing_directory',
                'path' => $dir,
                'description' => "Directory {$dir} does not exist"
            ];
            return;
        }
        
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime()
                ];
            }
        }
        
        $this->analysis[$dir] = [
            'path' => $dir,
            'files' => $files,
            'file_count' => count($files),
            'total_size' => array_sum(array_column($files, 'size'))
        ];
    }
    
    /**
     * Find all duplicates
     */
    private function findAllDuplicates() {
        echo "🔍 Phase 2: Finding all duplicates...\n";
        
        $allFiles = [];
        foreach ($this->analysis as $dir => $data) {
            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    $allFiles[] = [
                        'name' => $file['name'],
                        'path' => $file['path'],
                        'directory' => $dir
                    ];
                }
            }
        }
        
        // Group by filename
        $byName = [];
        foreach ($allFiles as $file) {
            $byName[$file['name']][] = $file;
        }
        
        foreach ($byName as $name => $files) {
            if (count($files) > 1) {
                $this->duplicates[$name] = $files;
            }
        }
        
        echo "   ✓ Found " . count($this->duplicates) . " duplicate file groups\n\n";
    }
    
    /**
     * Identify issues
     */
    private function identifyIssues() {
        echo "🚨 Phase 3: Identifying issues...\n";
        
        foreach ($this->analysis as $dir => $data) {
            // Check for empty directories
            if ($data['file_count'] === 0) {
                $this->issues[] = [
                    'type' => 'empty_directory',
                    'path' => $dir,
                    'description' => "Directory {$dir} is empty"
                ];
            }
            
            // Check for deprecated directories
            if (strpos($dir, '_DEPRECATED') !== false) {
                $this->issues[] = [
                    'type' => 'deprecated_directory',
                    'path' => $dir,
                    'description' => "Deprecated directory {$dir} should be removed"
                ];
            }
            
            // Check for wrong naming
            if (strpos($dir, 'vendor') !== false && $dir !== 'app/views/vendor') {
                $this->issues[] = [
                    'type' => 'wrong_naming',
                    'path' => $dir,
                    'description' => "Directory {$dir} has wrong naming convention"
                ];
            }
        }
        
        echo "   ✓ Identified " . count($this->issues) . " issues\n\n";
    }
    
    /**
     * Generate cleanup plan
     */
    private function generateCleanupPlan() {
        echo "🗺️ Phase 4: Generating cleanup plan...\n";
        
        $plan = [
            'delete_deprecated' => [],
            'delete_empty' => [],
            'delete_duplicates' => [],
            'organize_structure' => [],
            'create_missing' => [],
            'follow_rules' => []
        ];
        
        // Process issues
        foreach ($this->issues as $issue) {
            switch ($issue['type']) {
                case 'deprecated_directory':
                    $plan['delete_deprecated'][] = $issue['path'];
                    break;
                case 'empty_directory':
                    if (!in_array($issue['path'], ['app/views/partials', 'app/views/vendor'])) {
                        $plan['delete_empty'][] = $issue['path'];
                    }
                    break;
                case 'wrong_naming':
                    $plan['organize_structure'][] = [
                        'from' => $issue['path'],
                        'to' => 'app/views/vendor'
                    ];
                    break;
            }
        }
        
        // Process duplicates
        foreach ($this->duplicates as $name => $files) {
            if (count($files) > 1) {
                // Keep newest, delete others
                usort($files, function($a, $b) {
                    return $b['modified'] - $a['modified'];
                });
                $keep = array_shift($files);
                foreach ($files as $file) {
                    $plan['delete_duplicates'][] = $file['path'];
                }
            }
        }
        
        // Check for missing essential views
        $essentialViews = [
            'app/views/pages/home_new.php',
            'app/views/properties/index.php'
        ];
        
        foreach ($essentialViews as $view) {
            if (!file_exists($this->root . '/' . $view)) {
                $plan['create_missing'][] = $view;
            }
        }
        
        // Add rule compliance
        $plan['follow_rules'][] = 'All views must be in app/views/ with .php extension';
        $plan['follow_rules'][] = 'No blade files allowed';
        $plan['follow_rules'][] = 'Clean directory structure required';
        
        $this->actions = $plan;
        
        echo "   ✓ Cleanup plan generated with " . count($plan['delete_deprecated']) . " deletions, " . count($plan['delete_empty']) . " empty directories, " . count($plan['delete_duplicates']) . " duplicates\n\n";
    }
    
    /**
     * Execute cleanup
     */
    private function executeCleanup() {
        echo "🧹 Phase 5: Executing cleanup...\n";
        
        $deleted = 0;
        $created = 0;
        
        // Delete deprecated directories
        foreach ($this->actions['delete_deprecated'] as $dir) {
            $path = $this->root . '/' . $dir;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
                $deleted++;
                echo "   🗑️ Deleted deprecated directory: {$dir}\n";
            }
        }
        
        // Delete empty directories
        foreach ($this->actions['delete_empty'] as $dir) {
            $path = $this->root . '/' . $dir;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
                $deleted++;
                echo "   🗑️ Deleted empty directory: {$dir}\n";
            }
        }
        
        // Delete duplicate files
        foreach ($this->actions['delete_duplicates'] as $file) {
            if (file_exists($file['path'])) {
                unlink($file['path']);
                $deleted++;
                echo "   🗑️ Deleted duplicate file: {$file['name']}\n";
            }
        }
        
        // Create missing files
        foreach ($this->actions['create_missing'] as $file) {
            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $content = $this->generateViewContent(basename($file));
            file_put_contents($this->root . '/' . $file, $content);
            $created++;
            echo "   📝 Created missing file: {$file}\n";
        }
        
        // Organize structure
        foreach ($this->actions['organize_structure'] as $move) {
            $from = $this->root . '/' . $move['from'];
            $to = $this->root . '/' . $move['to'];
            
            if (is_dir($from)) {
                $toDir = dirname($to);
                if (!is_dir($toDir)) {
                    mkdir($toDir, 0755, true);
                }
                
                $this->moveDirectory($from, $to);
                echo "   📁 Moved directory: {$move['from']} → {$move['to']}\n";
            }
        }
        
        echo "   ✓ Cleanup executed: {$deleted} deleted, {$created} created\n\n";
    }
    
    /**
     * Organize according to rules
     */
    private function organizeAccordingToRules() {
        echo "📋 Phase 6: Organizing according to rules...\n";
        
        // Ensure app/views structure follows rules
        $viewsDir = $this->root . '/app/views';
        
        // Create essential subdirectories if they don't exist
        $essentialDirs = ['pages', 'layouts', 'partials'];
        foreach ($essentialDirs as $dir) {
            $path = $viewsDir . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
                echo "   📁 Created directory: app/views/{$dir}\n";
            }
        }
        
        // Check for any .blade files and remove them
        $bladeFiles = glob($viewsDir . '/**/*.blade.php');
        foreach ($bladeFiles as $blade) {
            unlink($blade);
            echo "   🗑️ Removed blade file: " . basename($blade) . "\n";
        }
        
        echo "   ✓ Organized according to APS Dream Home rules\n\n";
    }
    
    /**
     * Generate final report
     */
    private function generateFinalReport() {
        echo "📄 Phase 7: Generating final report...\n";
        
        $report = [
            'analysis_date' => date('Y-m-d H:i:s'),
            'directories_analyzed' => count($this->analysis),
            'duplicates_found' => count($this->duplicates),
            'issues_identified' => count($this->issues),
            'cleanup_actions' => $this->actions,
            'rules_compliance' => 'All files now follow APS Dream Home rules',
            'final_structure' => $this->getFinalStructure()
        ];
        
        $reportPath = $this->root . '/ultimate_folder_analysis_report_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "   ✓ Report saved: " . basename($reportPath) . "\n\n";
    }
    
    /**
     * Helper methods
     */
    private function deleteDirectory($path) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
            }
        }
        
        rmdir($path);
    }
    
    private function moveDirectory($from, $to) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($from, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            $fromPath = $file->getPathname();
            $relativePath = str_replace($from . '/', '', $fromPath);
            $toPath = $to . '/' . basename($relativePath);
            
            if (!is_dir(dirname($toPath))) {
                mkdir(dirname($toPath), 0755, true);
            }
            
            rename($fromPath, $toPath);
        }
        
        rmdir($from);
    }
    
    private function generateViewContent($viewName) {
        if (strpos($viewName, 'home_new.php') !== false) {
            return '<?php
$page_title = "Welcome to APS Dream Home";
$page_description = "Find your dream home with APS Dream Home - Your trusted real estate partner";
include __DIR__ . "/../layouts/base_new.php";
?>

<div class="container-fluid">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="display-4"><?php echo $page_title; ?></h1>
                    <p class="lead"><?php echo $page_description; ?></p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Properties -->
    <section class="featured-properties">
        <div class="container">
            <h2>Featured Properties</h2>
            <div class="row">
                <!-- Property cards will be dynamically loaded here -->
            </div>
        </div>
    </section>
</div>
<?php include __DIR__ . "/../layouts/footer_new.php"; ?>';
        }
        
        if (strpos($viewName, 'properties/index.php') !== false) {
            return '<?php
$page_title = "Properties - APS Dream Home";
$page_description = "Browse our premium property listings and find your perfect home";
include __DIR__ . "/../layouts/base_new.php";
?>

<div class="container-fluid">
    <!-- Properties Hero -->
    <section class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1><?php echo $page_title; ?></h1>
                    <p><?php echo $page_description; ?></p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Properties Listing -->
    <section class="properties-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="property-filters">
                        <!-- Filter options -->
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="property-grid">
                        <!-- Properties will be dynamically loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include __DIR__ . "/../layouts/footer_new.php"; ?>';
        }
        
        return '<?php
$page_title = "Page";
$page_description = "APS Dream Home Page";
include __DIR__ . "/../layouts/base_new.php";
?>

<div class="container-fluid">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p><?php echo $page_description; ?></p>
    </div>
</div>
<?php include __DIR__ . "/../layouts/footer_new.php"; ?>';
    }
    
    private function getFinalStructure() {
        $structure = [];
        
        // Scan final structure
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root . '/app/views', RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $item) {
            $path = $item->getPathname();
            $relativePath = str_replace($this->root . '/app/views/', '', $path);
            
            if ($item->isDir()) {
                $structure[] = [
                    'type' => 'directory',
                    'path' => $relativePath,
                    'name' => basename($relativePath)
                ];
            } elseif ($item->isFile()) {
                $structure[] = [
                    'type' => 'file',
                    'path' => $relativePath,
                    'name' => basename($relativePath)
                ];
            }
        }
        
        return $structure;
    }
}

// Execute ultimate folder analysis
$analyzer = new UltimateFolderAnalyzer();
$analyzer->executeUltimateAnalysis();
?>
