<?php
/**
 * APS Dream Home - Maximum Level Deep Project Scanner
 * Scans entire project for duplicates, organizes files, and creates unified structure
 */

require_once __DIR__ . '/base_new.php';

class MaxLevelProjectScanner {
    private $root;
    private $scannedFiles = [];
    private $duplicates = [];
    private $organized = [];
    private $stats = [
        'total_files' => 0,
        'duplicate_files' => 0,
        'organized_files' => 0,
        'deleted_files' => 0
    ];
    
    public function __construct($root = null) {
        $this->root = $root ?: dirname(__FILE__);
    }
    
    /**
     * Execute maximum level deep scan
     */
    public function executeMaxLevelScan() {
        echo "🚀 Starting Maximum Level Deep Project Scan...\n";
        echo "📁 Root Directory: {$this->root}\n\n";
        
        // Phase 1: Complete file scan
        $this->scanAllFiles();
        
        // Phase 2: Identify duplicates
        $this->identifyDuplicates();
        
        // Phase 3: Organize files
        $this->organizeFiles();
        
        // Phase 4: Generate report
        $this->generateReport();
        
        // Phase 5: Create organized structure
        $this->createOrganizedStructure();
        
        echo "\n✅ Maximum Level Deep Scan Complete!\n";
    }
    
    /**
     * Scan all files recursively
     */
    private function scanAllFiles() {
        echo "📊 Phase 1: Scanning all files...\n";
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $path = $file->getPathname();
                $relativePath = str_replace($this->root . '/', '', $path);
                $hash = $this->getFileHash($path);
                $size = $file->getSize();
                $extension = $file->getExtension();
                
                $this->scannedFiles[] = [
                    'path' => $path,
                    'relative_path' => $relativePath,
                    'hash' => $hash,
                    'size' => $size,
                    'extension' => $extension,
                    'name' => $file->getFilename(),
                    'directory' => dirname($relativePath),
                    'modified' => $file->getMTime()
                ];
            }
        }
        
        $this->stats['total_files'] = count($this->scannedFiles);
        echo "   ✓ Scanned {$this->stats['total_files']} files\n\n";
    }
    
    /**
     * Identify duplicate files by content and name
     */
    private function identifyDuplicates() {
        echo "🔍 Phase 2: Identifying duplicates...\n";
        
        // Group by hash (content duplicates)
        $hashGroups = [];
        foreach ($this->scannedFiles as $file) {
            $hashGroups[$file['hash']][] = $file;
        }
        
        // Find actual duplicates
        foreach ($hashGroups as $hash => $files) {
            if (count($files) > 1) {
                // Sort by modification time (keep newest)
                usort($files, function($a, $b) {
                    return $b['modified'] - $a['modified'];
                });
                
                $this->duplicates['content'][$hash] = [
                    'files' => $files,
                    'count' => count($files),
                    'total_size' => array_sum(array_column($files, 'size')),
                    'keep' => $files[0], // Keep newest
                    'delete' => array_slice($files, 1) // Delete older ones
                ];
            }
        }
        
        // Group by name (filename duplicates)
        $nameGroups = [];
        foreach ($this->scannedFiles as $file) {
            $nameGroups[$file['name']][] = $file;
        }
        
        foreach ($nameGroups as $name => $files) {
            if (count($files) > 1) {
                // Sort by modification time
                usort($files, function($a, $b) {
                    return $b['modified'] - $a['modified'];
                });
                
                $this->duplicates['name'][$name] = [
                    'files' => $files,
                    'count' => count($files),
                    'keep' => $files[0], // Keep newest
                    'delete' => array_slice($files, 1) // Delete older ones
                ];
            }
        }
        
        $totalDuplicates = count($this->duplicates['content'] ?? []) + count($this->duplicates['name'] ?? []);
        $this->stats['duplicate_files'] = $totalDuplicates;
        echo "   ✓ Found {$totalDuplicates} duplicate groups\n\n";
    }
    
    /**
     * Organize files by type and purpose
     */
    private function organizeFiles() {
        echo "📋 Phase 3: Organizing files...\n";
        
        $categories = [
            'controllers' => [],
            'models' => [],
            'views' => [],
            'layouts' => [],
            'routes' => [],
            'config' => [],
            'assets' => [],
            'public' => [],
            'vendor' => [],
            'logs' => [],
            'deprecated' => [],
            'other' => []
        ];
        
        foreach ($this->scannedFiles as $file) {
            $path = $file['relative_path'];
            $directory = $file['directory'];
            
            // Skip files marked for deletion
            $isMarkedForDeletion = false;
            foreach ($this->duplicates['delete'] ?? [] as $deleteList) {
                foreach ($deleteList as $deleteFile) {
                    if ($deleteFile['path'] === $file['path']) {
                        $isMarkedForDeletion = true;
                        break 2;
                    }
                }
            }
            
            if ($isMarkedForDeletion) {
                continue;
            }
            
            // Categorize files
            if (strpos($path, 'app/Http/Controllers') !== false) {
                $categories['controllers'][] = $file;
            } elseif (strpos($path, 'app/Models') !== false) {
                $categories['models'][] = $file;
            } elseif (strpos($path, 'app/views') !== false && strpos($path, 'layouts') === false) {
                $categories['views'][] = $file;
            } elseif (strpos($path, 'app/views/layouts') !== false) {
                $categories['layouts'][] = $file;
            } elseif (strpos($path, 'routes') !== false) {
                $categories['routes'][] = $file;
            } elseif (strpos($path, 'config') !== false) {
                $categories['config'][] = $file;
            } elseif (strpos($path, 'assets') !== false) {
                $categories['assets'][] = $file;
            } elseif (strpos($path, 'public') !== false) {
                $categories['public'][] = $file;
            } elseif (strpos($path, 'vendor') !== false) {
                $categories['vendor'][] = $file;
            } elseif (strpos($path, 'logs') !== false) {
                $categories['logs'][] = $file;
            } elseif (strpos($path, '_DEPRECATED') !== false || strpos($path, '_deprecated') !== false) {
                $categories['deprecated'][] = $file;
            } else {
                $categories['other'][] = $file;
            }
        }
        
        $this->organized = $categories;
        $this->stats['organized_files'] = array_sum(array_map('count', $categories));
        echo "   ✓ Organized {$this->stats['organized_files']} files into categories\n\n";
    }
    
    /**
     * Generate comprehensive report
     */
    private function generateReport() {
        echo "📄 Phase 4: Generating report...\n";
        
        $report = [
            'scan_date' => date('Y-m-d H:i:s'),
            'root_directory' => $this->root,
            'statistics' => $this->stats,
            'categories' => array_map('count', $this->organized),
            'duplicates' => [
                'content_duplicates' => count($this->duplicates['content'] ?? []),
                'name_duplicates' => count($this->duplicates['name'] ?? []),
                'total_duplicate_files' => array_sum(array_map(function($group) {
                    return count($group['delete'] ?? []);
                }, $this->duplicates['content'] ?? [])) + array_sum(array_map(function($group) {
                    return count($group['delete'] ?? []);
                }, $this->duplicates['name'] ?? []))
            ],
            'files_to_delete' => []
        ];
        
        // Collect files to delete
        foreach ($this->duplicates['delete'] ?? [] as $deleteList) {
            foreach ($deleteList as $file) {
                $report['files_to_delete'][] = $file['relative_path'];
            }
        }
        
        // Save report
        $reportPath = $this->root . '/project_scan_report_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        echo "   ✓ Report saved to: " . basename($reportPath) . "\n";
        echo "   📊 Statistics:\n";
        echo "     - Total files: {$report['statistics']['total_files']}\n";
        echo "     - Duplicate groups: {$report['duplicates']['content_duplicates']} (content) + {$report['duplicates']['name_duplicates']} (name)\n";
        echo "     - Files to delete: " . count($report['files_to_delete']) . "\n";
        echo "     - Organized files: {$report['statistics']['organized_files']}\n\n";
    }
    
    /**
     * Create organized structure
     */
    private function createOrganizedStructure() {
        echo "🏗️ Phase 5: Creating organized structure...\n";
        
        // Create organized directory structure
        $organizedDir = $this->root . '/_ORGANIZED';
        if (!is_dir($organizedDir)) {
            mkdir($organizedDir, 0755, true);
        }
        
        foreach ($this->organized as $category => $files) {
            if (empty($files)) continue;
            
            $categoryDir = $organizedDir . '/' . ucfirst($category);
            if (!is_dir($categoryDir)) {
                mkdir($categoryDir, 0755, true);
            }
            
            foreach ($files as $file) {
                $destPath = $categoryDir . '/' . $file['name'];
                copy($file['path'], $destPath);
            }
            
            echo "   ✓ Organized " . count($files) . " {$category} files\n";
        }
        
        echo "   ✓ Organized structure created in: _ORGANIZED/\n\n";
    }
    
    /**
     * Get file hash for duplicate detection
     */
    private function getFileHash($path) {
        return md5_file($path);
    }
    
    /**
     * Get scan results
     */
    public function getResults() {
        return [
            'scanned_files' => $this->scannedFiles,
            'duplicates' => $this->duplicates,
            'organized' => $this->organized,
            'statistics' => $this->stats
        ];
    }
}

// Execute scanner if run directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $scanner = new MaxLevelProjectScanner();
        $scanner->executeMaxLevelScan();
    } catch (Exception $e) {
        echo "❌ Scanner Error: " . $e->getMessage() . "\n";
        aps_log("Scanner error: " . $e->getMessage(), 'error');
    }
}

?>
