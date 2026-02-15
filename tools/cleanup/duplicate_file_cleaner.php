<?php
/**
 * Comprehensive Duplicate File Removal Utility
 * Scans and removes duplicate files across the project
 */

require_once __DIR__ . '/includes/logger.php';
require_once __DIR__ . '/includes/config_manager.php';

class DuplicateFileCleaner {
    private $logger;
    private $config;
    private $projectRoot;
    private $ignoredDirectories;
    private $ignoredExtensions;
    private $duplicateFiles = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->logger = new Logger();
        $this->config = ConfigManager::getInstance();
        $this->projectRoot = __DIR__;
        
        // Configurable ignore lists
        $this->ignoredDirectories = [
            '.git',
            'vendor',
            'node_modules',
            'logs',
            'cache',
            'temp'
        ];
        
        $this->ignoredExtensions = [
            '.log',
            '.tmp',
            '.cache'
        ];
    }

    /**
     * Find duplicate files
     * 
     * @return array List of duplicate files
     */
    public function findDuplicates() {
        $this->duplicateFiles = [];
        $fileHashes = [];

        // Recursive file scanning
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectRoot, 
                RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            // Skip ignored directories and files
            if ($this->shouldIgnoreFile($file)) {
                continue;
            }

            $filePath = $file->getPathname();
            $fileHash = $this->calculateFileHash($filePath);

            if ($fileHash === false) {
                continue; // Skip files that can't be hashed
            }

            if (!isset($fileHashes[$fileHash])) {
                $fileHashes[$fileHash] = [];
            }
            $fileHashes[$fileHash][] = $filePath;
        }

        // Identify true duplicates (more than one file with same hash)
        foreach ($fileHashes as $hash => $paths) {
            if (count($paths) > 1) {
                $this->duplicateFiles[] = [
                    'hash' => $hash,
                    'files' => $paths
                ];
            }
        }

        return $this->duplicateFiles;
    }

    /**
     * Check if file should be ignored
     * 
     * @param SplFileInfo $file File to check
     * @return bool Whether file should be ignored
     */
    private function shouldIgnoreFile($file) {
        $filePath = $file->getPathname();
        $fileName = $file->getFilename();

        // Ignore directories
        foreach ($this->ignoredDirectories as $ignoredDir) {
            if (strpos($filePath, $ignoredDir) !== false) {
                return true;
            }
        }

        // Ignore by extension
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array('.' . $fileExt, $this->ignoredExtensions)) {
            return true;
        }

        // Ignore non-files
        return !$file->isFile();
    }

    /**
     * Calculate file hash
     * 
     * @param string $filePath Path to file
     * @return string|false File hash or false
     */
    private function calculateFileHash($filePath) {
        // Prevent processing of very large files
        if (filesize($filePath) > 100 * 1024 * 1024) { // 100MB limit
            return false;
        }

        return hash_file('sha256', $filePath);
    }

    /**
     * Remove duplicate files
     * 
     * @param bool $keepNewest Keep newest file, delete older ones
     * @return array Removal results
     */
    public function removeDuplicates($keepNewest = true) {
        $removalResults = [
            'total_duplicates' => 0,
            'files_removed' => [],
            'errors' => []
        ];

        foreach ($this->duplicateFiles as $duplicateSet) {
            $files = $duplicateSet['files'];
            
            if (count($files) <= 1) {
                continue;
            }

            // Sort files by modification time if keeping newest
            if ($keepNewest) {
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
            }

            // Keep first file, remove others
            $keepFile = array_shift($files);

            foreach ($files as $duplicateFile) {
                try {
                    // Log before deletion
                    $this->logger->info('Removing duplicate file', [
                        'file' => $duplicateFile,
                        'original' => $keepFile
                    ]);

                    // Attempt file removal
                    if (unlink($duplicateFile)) {
                        $removalResults['files_removed'][] = $duplicateFile;
                        $removalResults['total_duplicates']++;
                    } else {
                        $removalResults['errors'][] = $duplicateFile;
                    }
                } catch (Exception $e) {
                    $removalResults['errors'][] = $duplicateFile;
                    $this->logger->error('Failed to remove duplicate', [
                        'file' => $duplicateFile,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $removalResults;
    }

    /**
     * Generate detailed report
     * 
     * @param array $results Removal results
     * @return string Formatted report
     */
    public function generateReport($results) {
        $report = "Duplicate File Removal Report\n";
        $report .= "===========================\n\n";
        $report .= "Total Duplicates Found: {$results['total_duplicates']}\n\n";
        
        $report .= "Removed Files:\n";
        foreach ($results['files_removed'] as $file) {
            $report .= "- $file\n";
        }

        if (!empty($results['errors'])) {
            $report .= "\nFiles Not Removed (Errors):\n";
            foreach ($results['errors'] as $file) {
                $report .= "- $file\n";
            }
        }

        return $report;
    }
}

// Execute duplicate file removal
try {
    $cleaner = new DuplicateFileCleaner();
    
    // Find duplicates
    $duplicates = $cleaner->findDuplicates();
    
    if (!empty($duplicates)) {
        // Remove duplicates
        $results = $cleaner->removeDuplicates();
        
        // Generate and log report
        $report = $cleaner->generateReport($results);
        
        // Log the report
        (new Logger())->info('Duplicate File Removal', [
            'report' => $report
        ]);
        
        // Optional: Write report to file
        file_put_contents(__DIR__ . '/logs/duplicate_removal_' . date('Y-m-d_H-i-s') . '.log', $report);
        
        echo $report;
    } else {
        echo "No duplicate files found.\n";
    }
} catch (Exception $e) {
    // Log any unexpected errors
    (new Logger())->critical('Duplicate File Removal Failed', [
        'error' => $e->getMessage()
    ]);
    
    echo "Error: " . $e->getMessage() . "\n";
}
