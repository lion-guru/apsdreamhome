<?php
/**
 * Parallel Operations for APS Dream Home
 * Speeds up file operations and local tool execution
 */

class ParallelOperations {
    private $projectPath;
    private $cache = [];
    private $stats = [
        'file_reads' => 0,
        'file_writes' => 0,
        'cached_operations' => 0,
        'batch_operations' => 0
    ];
    
    public function __construct($projectPath) {
        $this->projectPath = $projectPath;
    }
    
    /**
     * Read multiple files in batch
     */
    public function batchReadFiles(array $filePaths) {
        $results = [];
        echo "BATCH READING " . count($filePaths) . " files...\n";
        
        foreach ($filePaths as $path) {
            $fullPath = $this->projectPath . '/' . $path;
            
            // Check cache first
            if (isset($this->cache[$fullPath])) {
                $results[$path] = $this->cache[$fullPath];
                $this->stats['cached_operations']++;
                continue;
            }
            
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                $this->cache[$fullPath] = $content;
                $results[$path] = $content;
                $this->stats['file_reads']++;
            } else {
                $results[$path] = null;
            }
        }
        
        $this->stats['batch_operations']++;
        return $results;
    }
    
    /**
     * Write multiple files in batch
     */
    public function batchWriteFiles(array $fileData) {
        $results = [];
        echo "BATCH WRITING " . count($fileData) . " files...\n";
        
        foreach ($fileData as $path => $content) {
            $fullPath = $this->projectPath . '/' . $path;
            $dir = dirname($fullPath);
            
            // Ensure directory exists
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $success = file_put_contents($fullPath, $content);
            $results[$path] = $success !== false;
            
            // Update cache
            if ($success !== false) {
                $this->cache[$fullPath] = $content;
            }
            
            $this->stats['file_writes']++;
        }
        
        $this->stats['batch_operations']++;
        return $results;
    }
    
    /**
     * Execute PHP script with caching
     */
    public function executeCachedScript($scriptPath, $args = []) {
        $cacheKey = $scriptPath . ':' . md5(serialize($args));
        
        if (isset($this->cache[$cacheKey])) {
            echo "USING CACHED RESULT for $scriptPath\n";
            $this->stats['cached_operations']++;
            return $this->cache[$cacheKey];
        }
        
        echo "EXECUTING: php $scriptPath\n";
        
        // Build command
        $command = "php $scriptPath";
        foreach ($args as $arg) {
            $command .= " " . escapeshellarg($arg);
        }
        
        $output = shell_exec($command);
        
        // Cache result
        $this->cache[$cacheKey] = $output;
        
        return $output;
    }
    
    /**
     * Execute MySQL query efficiently
     */
    public function executeMySQL($query, $database = null) {
        echo "EXECUTING MYSQL QUERY\n";
        
        // Build mysql command
        $command = "mysql -u root";
        if ($database) {
            $command .= " -D $database";
        }
        $command .= " -e " . escapeshellarg($query);
        
        $output = shell_exec($command);
        
        return $output;
    }
    
    /**
     * Scan directory structure efficiently
     */
    public function scanDirectory($path, $patterns = ['*.php', '*.js', '*.css']) {
        echo "SCANNING: $path\n";
        
        $files = [];
        $patternString = implode(' ', $patterns);
        
        // Use find command for faster scanning
        $command = "find $path -type f \\( " . 
                   implode(' -o ', array_map(function($p) {
                       return "-name '$p'";
                   }, $patterns)) . 
                   " \\) 2>/dev/null";
        
        $output = shell_exec($command);
        if ($output) {
            $files = array_filter(explode("\n", trim($output)));
            $files = array_values($files); // Re-index
        }
        
        return $files;
    }
    
    /**
     * Get file statistics
     */
    public function getFileStats($path) {
        $stats = [
            'total_files' => 0,
            'by_extension' => [],
            'total_size' => 0
        ];
        
        $files = $this->scanDirectory($path, ['*']);
        $stats['total_files'] = count($files);
        
        foreach ($files as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if (!isset($stats['by_extension'][$extension])) {
                $stats['by_extension'][$extension] = 0;
            }
            $stats['by_extension'][$extension]++;
            $stats['total_size'] += filesize($file);
        }
        
        return $stats;
    }
    
    /**
     * Clear cache
     */
    public function clearCache() {
        $this->cache = [];
        echo "CACHE CLEARED\n";
    }
    
    /**
     * Get statistics
     */
    public function getStats() {
        return $this->stats;
    }
    
    /**
     * Generate performance report
     */
    public function generateReport() {
        echo "\n=== PERFORMANCE REPORT ===\n";
        echo "File Reads: " . $this->stats['file_reads'] . "\n";
        echo "File Writes: " . $this->stats['file_writes'] . "\n";
        echo "Cached Operations: " . $this->stats['cached_operations'] . "\n";
        echo "Batch Operations: " . $this->stats['batch_operations'] . "\n";
        
        $cacheHitRate = 0;
        $totalOps = $this->stats['file_reads'] + $this->stats['cached_operations'];
        if ($totalOps > 0) {
            $cacheHitRate = round(($this->stats['cached_operations'] / $totalOps) * 100, 2);
        }
        
        echo "Cache Hit Rate: $cacheHitRate%\n";
        echo "Cache Size: " . count($this->cache) . " items\n";
        echo "==========================\n";
    }
}

// Usage example
$ops = new ParallelOperations(__DIR__ . '/..');

// Batch read example
$files = [
    'app/Config/routes.php',
    'app/Core/App.php',
    'public/index.php'
];

$contents = $ops->batchReadFiles($files);

// Get statistics
$stats = $ops->getStats();

// Generate report
$ops->generateReport();
?>