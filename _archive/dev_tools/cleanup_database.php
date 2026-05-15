<?php
/**
 * APS Dream Home - Database Cleanup Tool
 * Safe database cleanup and optimization
 * Recreated after file deletion
 */

echo "🧹 APS DREAM HOME - DATABASE CLEANUP\n";
echo "==================================\n";

class DatabaseCleanup {
    private $pdo;
    private $dbname;
    
    public function __construct() {
        $host = 'localhost';
        $this->dbname = 'apsdreamhome';
        $username = 'root';
        $password = '';
        
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$this->dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "✅ Database Connection: Established\n";
        } catch (Exception $e) {
            echo "❌ Database Connection: Failed - " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    /**
     * Clean up orphaned records
     */
    public function cleanupOrphanedRecords() {
        echo "\n1. Cleaning Orphaned Records:\n";
        
        $cleanupQueries = [
            'users' => [
                'table' => 'users',
                'check_column' => 'id',
                'reference_table' => 'user_profiles',
                'reference_column' => 'user_id'
            ],
            'properties' => [
                'table' => 'properties',
                'check_column' => 'id',
                'reference_table' => 'property_images',
                'reference_column' => 'property_id'
            ],
            'leads' => [
                'table' => 'leads',
                'check_column' => 'id',
                'reference_table' => 'lead_followups',
                'reference_column' => 'lead_id'
            ]
        ];
        
        foreach ($cleanupQueries as $name => $query) {
            try {
                // Check if tables exist
                $tableCheck = $this->pdo->query("SHOW TABLES LIKE '{$query['table']}'");
                $refTableCheck = $this->pdo->query("SHOW TABLES LIKE '{$query['reference_table']}'");
                
                if ($tableCheck->rowCount() > 0 && $refTableCheck->rowCount() > 0) {
                    $sql = "DELETE t1 FROM {$query['table']} t1 
                            LEFT JOIN {$query['reference_table']} t2 
                            ON t1.{$query['check_column']} = t2.{$query['reference_column']} 
                            WHERE t2.{$query['reference_column']} IS NULL";
                    
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();
                    $affected = $stmt->rowCount();
                    
                    echo "✅ $name: Cleaned $affected orphaned records\n";
                } else {
                    echo "⚠️ $name: Tables not found, skipping\n";
                }
            } catch (Exception $e) {
                echo "❌ $name: Error - " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Optimize database tables
     */
    public function optimizeTables() {
        echo "\n2. Optimizing Database Tables:\n";
        
        try {
            $stmt = $this->pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                try {
                    // Analyze table
                    $this->pdo->query("ANALYZE TABLE `$table`");
                    echo "✅ $table: Analyzed\n";
                    
                    // Optimize table
                    $this->pdo->query("OPTIMIZE TABLE `$table`");
                    echo "✅ $table: Optimized\n";
                    
                } catch (Exception $e) {
                    echo "❌ $table: Optimization failed - " . $e->getMessage() . "\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ Table Listing: Failed - " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Clean up old logs
     */
    public function cleanupOldLogs() {
        echo "\n3. Cleaning Old Logs:\n";
        
        $logTables = [
            'error_logs' => 30, // Keep last 30 days
            'activity_logs' => 90, // Keep last 90 days
            'access_logs' => 7 // Keep last 7 days
        ];
        
        foreach ($logTables as $table => $days) {
            try {
                // Check if table exists
                $tableCheck = $this->pdo->query("SHOW TABLES LIKE '$table'");
                
                if ($tableCheck->rowCount() > 0) {
                    $sql = "DELETE FROM `$table` WHERE created_at < DATE_SUB(NOW(), INTERVAL $days DAY)";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute();
                    $affected = $stmt->rowCount();
                    
                    echo "✅ $table: Deleted $affected old records (last $days days)\n";
                } else {
                    echo "⚠️ $table: Table not found, skipping\n";
                }
            } catch (Exception $e) {
                echo "❌ $table: Cleanup failed - " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * Clean up temporary files
     */
    public function cleanupTempFiles() {
        echo "\n4. Cleaning Temporary Files:\n";
        
        $tempDirs = [
            __DIR__ . '/../storage/temp',
            __DIR__ . '/../storage/cache',
            __DIR__ . '/../storage/sessions'
        ];
        
        foreach ($tempDirs as $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '/*');
                $deleted = 0;
                
                foreach ($files as $file) {
                    if (is_file($file) && (time() - filemtime($file)) > 86400) { // Older than 24 hours
                        if (unlink($file)) {
                            $deleted++;
                        }
                    }
                }
                
                echo "✅ " . basename($dir) . ": Deleted $deleted temporary files\n";
            } else {
                echo "⚠️ " . basename($dir) . ": Directory not found\n";
            }
        }
    }
    
    /**
     * Update database statistics
     */
    public function updateStatistics() {
        echo "\n5. Updating Database Statistics:\n";
        
        try {
            // Update table statistics
            $this->pdo->query("FLUSH TABLES");
            echo "✅ Table Statistics: Updated\n";
            
            // Reset auto-increment values
            $autoIncrementTables = ['users', 'properties', 'leads', 'projects'];
            
            foreach ($autoIncrementTables as $table) {
                try {
                    $tableCheck = $this->pdo->query("SHOW TABLES LIKE '$table'");
                    
                    if ($tableCheck->rowCount() > 0) {
                        $stmt = $this->pdo->query("SELECT MAX(id) as max_id FROM `$table`");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $maxId = $result['max_id'] ?? 0;
                        
                        if ($maxId > 0) {
                            $this->pdo->query("ALTER TABLE `$table` AUTO_INCREMENT = " . ($maxId + 1));
                            echo "✅ $table: Auto-increment updated to " . ($maxId + 1) . "\n";
                        }
                    }
                } catch (Exception $e) {
                    echo "❌ $table: Auto-increment update failed - " . $e->getMessage() . "\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Statistics Update: Failed - " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Generate cleanup report
     */
    public function generateReport() {
        echo "\n📊 CLEANUP REPORT:\n";
        echo "==================\n";
        
        try {
            // Get database size
            $stmt = $this->pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS db_size FROM information_schema.tables WHERE table_schema = '$this->dbname'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "📦 Database Size: {$result['db_size']} MB\n";
            
            // Get table count
            $stmt = $this->pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '$this->dbname'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "📋 Total Tables: {$result['table_count']}\n";
            
            // Get record counts
            $tables = ['users', 'properties', 'leads', 'projects'];
            foreach ($tables as $table) {
                try {
                    $tableCheck = $this->pdo->query("SHOW TABLES LIKE '$table'");
                    
                    if ($tableCheck->rowCount() > 0) {
                        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `$table`");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo "📊 $table: {$result['count']} records\n";
                    }
                } catch (Exception $e) {
                    echo "❌ $table: Count failed\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Report Generation: Failed - " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Create backup before cleanup
     */
    public function createBackup() {
        echo "\n🔒 Creating Backup Before Cleanup:\n";
        
        $backupFile = __DIR__ . "/../backups/backup_before_cleanup_" . date('Y-m-d_H-i-s') . ".sql";
        $backupDir = dirname($backupFile);
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $command = "mysqldump -u root --single-transaction --routines --triggers $this->dbname > $backupFile";
        
        try {
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                echo "✅ Backup Created: $backupFile\n";
                echo "📦 Backup Size: " . number_format(filesize($backupFile) / 1024 / 1024, 2) . " MB\n";
                return true;
            } else {
                echo "❌ Backup Failed: Return code $returnCode\n";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ Backup Error: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Main execution
$cleanup = new DatabaseCleanup();

// Create backup first
if ($cleanup->createBackup()) {
    echo "\n🚀 Starting Database Cleanup...\n";
    
    // Perform cleanup operations
    $cleanup->cleanupOrphanedRecords();
    $cleanup->optimizeTables();
    $cleanup->cleanupOldLogs();
    $cleanup->cleanupTempFiles();
    $cleanup->updateStatistics();
    
    // Generate report
    $cleanup->generateReport();
    
    echo "\n✅ DATABASE CLEANUP COMPLETE!\n";
    echo "Database optimized and ready for production.\n";
} else {
    echo "\n❌ CLEANUP ABORTED: Backup failed\n";
    echo "Please check database connection and permissions.\n";
}
?>
