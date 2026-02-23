<?php
/**
 * Comprehensive Duplicate File Cleanup Script
 * Identifies and removes duplicate files across various project directories
 */
class ComprehensiveDuplicateCleanup {
    private $projectRoot;
    private $logFile;
    private $backupDir;
    private $duplicateFiles = [];
    private $ignoredDirectories = [
        '.git', 'vendor', 'node_modules', 'backup_duplicates', 
        'backups', 'logs', '.vscode', 'nbproject'
    ];

    public function __construct($projectRoot = null) {
        $this->projectRoot = $projectRoot ?? __DIR__;
        $this->logFile = $this->projectRoot . '/logs/comprehensive_duplicate_cleanup_' . date('Y-m-d') . '.log';
        $this->backupDir = $this->projectRoot . '/backups/duplicate_files_' . date('Y-m-d_H-i-s');

        // Ensure log and backup directories exist
        $this->ensureDirectoriesExist();
    }

    /**
     * Ensure log and backup directories exist
     */
    private function ensureDirectoriesExist() {
        $directories = [
            dirname($this->logFile),
            $this->backupDir
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Log messages to the log file
     * @param string $message
     */
    private function log($message) {
        $logEntry = date('Y-m-d H:i:s') . ": $message\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
        echo $logEntry;
    }

    /**
     * Check if a directory should be ignored
     * @param string $path
     * @return bool
     */
    private function shouldIgnoreDirectory($path) {
        foreach ($this->ignoredDirectories as $ignoredDir) {
            if (strpos($path, $ignoredDir) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Find duplicate files based on content hash
     * @return array
     */
    public function findDuplicateFiles() {
        $fileHashes = [];
        $duplicates = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, 
            RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $path = $file->getPathname();

            // Skip ignored directories and non-files
            if ($this->shouldIgnoreDirectory($path) || !$file->isFile()) {
                continue;
            }

            // Compute file hash
            $hash = hash_file('md5', $path);
            
            if (isset($fileHashes[$hash])) {
                $duplicates[$hash][] = $path;
            } else {
                $fileHashes[$hash] = $path;
            }
        }

        return $duplicates;
    }

    /**
     * Remove duplicate files, keeping the first occurrence
     */
    public function removeDuplicates() {
        $duplicates = $this->findDuplicateFiles();

        foreach ($duplicates as $hash => $files) {
            // Keep the first file, remove others
            $keepFile = array_shift($files);
            
            foreach ($files as $duplicateFile) {
                // Backup before deletion
                $backupPath = str_replace($this->projectRoot, $this->backupDir, $duplicateFile);
                $backupDir = dirname($backupPath);
                
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                
                // Copy to backup
                copy($duplicateFile, $backupPath);
                
                // Remove duplicate
                unlink($duplicateFile);
                
                $this->log("Removed duplicate: $duplicateFile (Backup: $backupPath)");
            }
        }
    }

    /**
     * Generate a detailed report of duplicate files
     */
    public function generateReport() {
        $duplicates = $this->findDuplicateFiles();
        
        $reportFile = $this->projectRoot . '/logs/duplicate_files_report_' . date('Y-m-d') . '.csv';
        $report = fopen($reportFile, 'w');
        
        // CSV headers
        fputcsv($report, ['Hash', 'Duplicate Files']);
        
        foreach ($duplicates as $hash => $files) {
            fputcsv($report, [$hash, implode('; ', $files)]);
        }
        
        fclose($report);
        
        $this->log("Duplicate files report generated: $reportFile");
    }

    /**
     * Run the full cleanup process
     */
    public function cleanup() {
        $this->log("Starting comprehensive duplicate file cleanup");
        $this->removeDuplicates();
        $this->generateReport();
        $this->log("Duplicate file cleanup completed");
    }
}

// Usage example
$cleanup = new ComprehensiveDuplicateCleanup();
$cleanup->cleanup();
?>
