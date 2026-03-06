<?php
/**
 * APS Dream Home - Simple Project Scanner
 * Fast and efficient duplicate detection and file organization
 */

// Simple file scanner without complex recursion
class SimpleProjectScanner {
    private $root;
    private $files = [];
    private $duplicates = [];
    
    public function __construct($root = null) {
        $this->root = $root ?: __DIR__;
    }
    
    public function scan() {
        echo "🚀 Starting Simple Project Scan...\n";
        
        // Scan key directories only
        $dirs = [
            'app/Http/Controllers',
            'app/views',
            'app/views/layouts', 
            'routes',
            'public',
            '_DEPRECATED'
        ];
        
        foreach ($dirs as $dir) {
            if (is_dir($this->root . '/' . $dir)) {
                $this->scanDirectory($dir);
            }
        }
        
        // Find duplicates
        $this->findDuplicates();
        
        // Show results
        $this->showResults();
        
        echo "✅ Scan Complete!\n";
    }
    
    private function scanDirectory($dir) {
        $fullPath = $this->root . '/' . $dir;
        $items = scandir($fullPath);
        
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            
            $itemPath = $fullPath . '/' . $item;
            
            if (is_file($itemPath)) {
                $this->files[] = [
                    'path' => $itemPath,
                    'name' => $item,
                    'dir' => $dir,
                    'size' => filesize($itemPath),
                    'hash' => md5_file($itemPath)
                ];
            }
        }
    }
    
    private function findDuplicates() {
        // Group by filename
        $byName = [];
        foreach ($this->files as $file) {
            $byName[$file['name']][] = $file;
        }
        
        foreach ($byName as $name => $files) {
            if (count($files) > 1) {
                $this->duplicates[$name] = $files;
            }
        }
    }
    
    private function showResults() {
        echo "\n📊 Results:\n";
        echo "Total files scanned: " . count($this->files) . "\n";
        echo "Duplicate groups found: " . count($this->duplicates) . "\n\n";
        
        if (!empty($this->duplicates)) {
            echo "🔍 Duplicate Files:\n";
            foreach ($this->duplicates as $name => $files) {
                echo "  📄 {$name} (x" . count($files) . "):\n";
                foreach ($files as $file) {
                    echo "    - {$file['dir']}/{$file['name']} (" . $this->formatBytes($file['size']) . ")\n";
                }
                echo "\n";
            }
        }
    }
    
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Run scanner
$scanner = new SimpleProjectScanner();
$scanner->scan();
?>
