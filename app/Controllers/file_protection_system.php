<?php
/**
 * APS Dream Home - File Protection System
 * Prevents accidental file deletions and provides automated recovery
 */

echo "🛡️ APS DREAM HOME - FILE PROTECTION SYSTEM\n";
echo "========================================\n";

class FileProtectionSystem {
    private $protectedDirs = ['tests', 'tools', 'config', 'logs'];
    private $backupDir;
    private $logFile;
    
    public function __construct() {
        $this->backupDir = __DIR__ . '/backups';
        $this->logFile = __DIR__ . '/logs/protection.log';
        
        // Create directories
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }
    
    /**
     * Create snapshot of protected directories
     */
    public function createSnapshot() {
        $timestamp = date('Y-m-d_H-i-s');
        $snapshotDir = $this->backupDir . "/snapshot_$timestamp";
        
        echo "📸 Creating snapshot: $timestamp\n";
        
        foreach ($this->protectedDirs as $dir) {
            if (is_dir(__DIR__ . "/$dir")) {
                $this->copyDirectory(__DIR__ . "/$dir", "$snapshotDir/$dir");
                echo "✅ Backed up: $dir\n";
            }
        }
        
        // Clean old backups (keep last 10)
        $this->cleanupOldBackups();
        
        // Save snapshot info
        $snapshotInfo = [
            'timestamp' => $timestamp,
            'directories' => $this->getDirectoryStats(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        file_put_contents("$snapshotDir/info.json", json_encode($snapshotInfo, JSON_PRETTY_PRINT));
        
        $this->log("Snapshot created: $timestamp");
        return $timestamp;
    }
    
    /**
     * Monitor file changes
     */
    public function startMonitoring() {
        echo "👁️ Starting file monitoring...\n";
        
        $lastSnapshot = $this->createSnapshot();
        
        while (true) {
            sleep(30); // Check every 30 seconds
            
            $currentStats = $this->getDirectoryStats();
            $lastStats = $this->getLastSnapshotStats($lastSnapshot);
            
            $changes = $this->compareStats($lastStats, $currentStats);
            
            if (!empty($changes)) {
                $this->handleChanges($changes, $lastSnapshot);
                $lastSnapshot = $this->createSnapshot();
            }
        }
    }
    
    /**
     * Handle detected changes
     */
    private function handleChanges($changes, $snapshotId) {
        echo "🚨 CHANGES DETECTED!\n";
        
        foreach ($changes as $change) {
            echo "  - {$change['type']}: {$change['path']}\n";
        }
        
        // Log changes
        $this->log(json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'snapshot_id' => $snapshotId,
            'changes' => $changes
        ], JSON_PRETTY_PRINT));
        
        // Auto-restore deleted files
        $deletedFiles = array_filter($changes, function($change) {
            return $change['type'] === 'DELETED';
        });
        
        if (!empty($deletedFiles)) {
            $this->autoRestore($snapshotId);
        }
    }
    
    /**
     * Auto-restore from snapshot
     */
    public function autoRestore($snapshotId) {
        echo "🔄 Auto-restoring from snapshot: $snapshotId\n";
        
        $snapshotDir = $this->backupDir . "/snapshot_$snapshotId";
        
        if (!is_dir($snapshotDir)) {
            echo "❌ Snapshot not found: $snapshotId\n";
            return false;
        }
        
        foreach ($this->protectedDirs as $dir) {
            $sourceDir = "$snapshotDir/$dir";
            $targetDir = __DIR__ . "/$dir";
            
            if (is_dir($sourceDir)) {
                if (is_dir($targetDir)) {
                    // Backup current state before restore
                    $emergencyBackup = $targetDir . '_emergency_backup_' . date('His');
                    rename($targetDir, $emergencyBackup);
                    echo "📦 Emergency backup created: $emergencyBackup\n";
                }
                
                // Restore from snapshot
                $this->copyDirectory($sourceDir, $targetDir);
                echo "✅ Restored: $dir\n";
            }
        }
        
        $this->log("Auto-restored from snapshot: $snapshotId");
        return true;
    }
    
    /**
     * Get directory statistics
     */
    private function getDirectoryStats() {
        $stats = [];
        
        foreach ($this->protectedDirs as $dir) {
            $stats[$dir] = $this->scanDirectory(__DIR__ . "/$dir");
        }
        
        return $stats;
    }
    
    /**
     * Scan directory recursively
     */
    private function scanDirectory($dir) {
        $files = [];
        
        if (!is_dir($dir)) {
            return $files;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace(__DIR__ . '/', '', $file->getPathname());
                $files[$relativePath] = [
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                    'hash' => md5_file($file->getPathname())
                ];
            }
        }
        
        return $files;
    }
    
    /**
     * Compare directory statistics
     */
    private function compareStats($oldStats, $newStats) {
        $changes = [];
        
        foreach ($this->protectedDirs as $dir) {
            $oldFiles = $oldStats[$dir] ?? [];
            $newFiles = $newStats[$dir] ?? [];
            
            // Check for deleted files
            foreach ($oldFiles as $file => $info) {
                if (!isset($newFiles[$file])) {
                    $changes[] = [
                        'type' => 'DELETED',
                        'path' => $file,
                        'directory' => $dir,
                        'info' => $info
                    ];
                }
            }
            
            // Check for modified files
            foreach ($newFiles as $file => $info) {
                if (isset($oldFiles[$file])) {
                    if ($oldFiles[$file]['hash'] !== $info['hash']) {
                        $changes[] = [
                            'type' => 'MODIFIED',
                            'path' => $file,
                            'directory' => $dir,
                            'old_info' => $oldFiles[$file],
                            'new_info' => $info
                        ];
                    }
                } else {
                    $changes[] = [
                        'type' => 'ADDED',
                        'path' => $file,
                        'directory' => $dir,
                        'info' => $info
                    ];
                }
            }
        }
        
        return $changes;
    }
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $destination) {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $destDir = $destination . '/' . $file->getFilename();
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
            } elseif ($file->isFile()) {
                $destFile = $destination . '/' . $iterator->getSubPathName();
                copy($file->getPathname(), $destFile);
            }
        }
    }
    
    /**
     * Clean old backups
     */
    private function cleanupOldBackups() {
        $snapshots = glob($this->backupDir . '/snapshot_*', GLOB_ONLYDIR);
        
        if (count($snapshots) > 10) {
            // Sort by creation time
            usort($snapshots, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Delete oldest snapshots
            $toDelete = array_slice($snapshots, 0, count($snapshots) - 10);
            
            foreach ($toDelete as $snapshot) {
                $this->removeDirectory($snapshot);
                echo "🗑️ Deleted old snapshot: " . basename($snapshot) . "\n";
            }
        }
    }
    
    /**
     * Remove directory recursively
     */
    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Get last snapshot stats
     */
    private function getLastSnapshotStats($snapshotId) {
        $snapshotDir = $this->backupDir . "/snapshot_$snapshotId";
        $infoFile = "$snapshotDir/info.json";
        
        if (file_exists($infoFile)) {
            $info = json_decode(file_get_contents($infoFile), true);
            return $info['directories'] ?? [];
        }
        
        return [];
    }
    
    /**
     * Log protection events
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * List available snapshots
     */
    public function listSnapshots() {
        $snapshots = glob($this->backupDir . '/snapshot_*', GLOB_ONLYDIR);
        
        echo "\n📸 Available Snapshots:\n";
        echo "========================\n";
        
        foreach ($snapshots as $snapshot) {
            $snapshotId = str_replace([$this->backupDir . '/snapshot_'], '', $snapshot);
            $infoFile = "$snapshot/info.json";
            
            if (file_exists($infoFile)) {
                $info = json_decode(file_get_contents($infoFile), true);
                echo "📅 $snapshotId - {$info['created_at']}\n";
                
                foreach ($info['directories'] as $dir => $files) {
                    echo "   $dir: " . count($files) . " files\n";
                }
            }
            echo "\n";
        }
    }
}

// Main execution
$protection = new FileProtectionSystem();

// Check command line arguments
$action = $argv[1] ?? 'help';

switch ($action) {
    case 'snapshot':
        $protection->createSnapshot();
        break;
        
    case 'monitor':
        $protection->startMonitoring();
        break;
        
    case 'restore':
        $snapshotId = $argv[2] ?? '';
        if ($snapshotId) {
            $protection->autoRestore($snapshotId);
        } else {
            echo "❌ Please provide snapshot ID\n";
            echo "Usage: php file_protection_system.php restore <snapshot_id>\n";
        }
        break;
        
    case 'list':
        $protection->listSnapshots();
        break;
        
    case 'help':
    default:
        echo "🛡️ File Protection System Commands:\n";
        echo "=================================\n";
        echo "snapshot     - Create a new snapshot\n";
        echo "monitor      - Start real-time monitoring\n";
        echo "restore <id> - Restore from snapshot\n";
        echo "list         - List available snapshots\n";
        echo "help         - Show this help\n";
        break;
}

echo "\n✅ File Protection System: Ready\n";
?>
