<?php
/**
 * Partition Maintenance Script - Yearly Cron Job
 * 
 * Purpose: Auto-create new partitions for upcoming years, archive old partitions
 * Works with generated column partitioning (created_at_year / due_date_year)
 * 
 * Schedule: Run once per year (e.g., January 1st)
 * 
 * Usage: 
 *   php scripts/partition_maintenance.php              # Dry run
 *   php scripts/partition_maintenance.php --execute    # Actually apply changes
 *   php scripts/partition_maintenance.php --archive    # Archive old partitions (10+ years)
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

$dryRun = true;
$archiveMode = false;
$yearsFuture = 5;
$archiveYearsOld = 10;

foreach ($argv as $arg) {
    if ($arg === '--execute') $dryRun = false;
    if ($arg === '--archive') $archiveMode = true;
    if (strpos($arg, '--years-future=') === 0) $yearsFuture = (int)substr($arg, 15);
    if (strpos($arg, '--archive-years=') === 0) $archiveYearsOld = (int)substr($arg, 16);
}

$db = Database::getInstance();
$pdo = $db->getPdo();

$tables = [
    'mlm_commission_ledger' => ['year_column' => 'created_at_year', 'partition_prefix' => 'p'],
    'booking_payment_schedules' => ['year_column' => 'due_date_year', 'partition_prefix' => 'p'],
];

echo "=== Partition Maintenance ===\n";
echo "Mode: " . ($dryRun ? 'DRY RUN' : 'EXECUTE') . "\n";
echo "Archive mode: " . ($archiveMode ? 'ON' : 'OFF') . "\n";
echo "Future years to maintain: $yearsFuture\n";
if ($archiveMode) echo "Archive partitions older than: $archiveYearsOld years\n";
echo "\n";

$currentYear = (int)date('Y');
$targetMaxYear = $currentYear + $yearsFuture;
$archiveBeforeYear = $currentYear - $archiveYearsOld;

foreach ($tables as $table => $config) {
    echo "Processing table: $table\n";
    $yearCol = $config['year_column'];
    $prefix = $config['partition_prefix'];
    
    // Check if table is partitioned
    $stmt = $pdo->prepare("
        SELECT PARTITION_METHOD FROM information_schema.PARTITIONS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND PARTITION_NAME IS NOT NULL
        LIMIT 1
    ");
    $stmt->execute([$table]);
    $method = $stmt->fetchColumn();
    
    if (!$method || $method === 'NULL') {
        echo "  Table $table is NOT partitioned, skipping.\n\n";
        continue;
    }
    
    // Get existing partitions
    $stmt = $pdo->prepare("
        SELECT PARTITION_NAME, PARTITION_DESCRIPTION
        FROM information_schema.PARTITIONS
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = ?
        AND PARTITION_NAME IS NOT NULL
        AND PARTITION_NAME != ''
        ORDER BY PARTITION_ORDINAL_POSITION
    ");
    $stmt->execute([$table]);
    $partitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "  Existing partitions: " . count($partitions) . "\n";
    
    // Find max year partition
    $maxYear = $currentYear;
    foreach ($partitions as $part) {
        if (preg_match('/^' . $prefix . '(\d+)$/', $part['PARTITION_NAME'], $matches)) {
            $year = (int)$matches[1];
            if ($year > $maxYear && $year < 9999) $maxYear = $year;
        }
    }
    
    echo "  Current max year partition: $maxYear\n";
    echo "  Target max year: $targetMaxYear\n";
    
    // Add missing future year partitions
    $addedCount = 0;
    for ($year = $maxYear + 1; $year <= $targetMaxYear; $year++) {
        $nextYear = $year + 1;
        $partitionName = "{$prefix}{$year}";
        
        // Check if partition already exists
        $exists = false;
        foreach ($partitions as $part) {
            if ($part['PARTITION_NAME'] === $partitionName) {
                $exists = true;
                break;
            }
        }
        
        if ($exists) {
            echo "  Partition $partitionName already exists, skipping.\n";
            continue;
        }
        
        $alterSql = "ALTER TABLE $table ADD PARTITION (
            PARTITION $partitionName VALUES LESS THAN ($nextYear)
            (SUBPARTITION {$partitionName}_s0 ENGINE=InnoDB,
             SUBPARTITION {$partitionName}_s1 ENGINE=InnoDB,
             SUBPARTITION {$partitionName}_s2 ENGINE=InnoDB,
             SUBPARTITION {$partitionName}_s3 ENGINE=InnoDB)
        )";
        
        echo "  " . ($dryRun ? '[DRY RUN] Would add' : 'Adding') . " partition: $partitionName (year $year)\n";
        
        if (!$dryRun) {
            try {
                $pdo->exec($alterSql);
                echo "    -> SUCCESS\n";
            } catch (PDOException $e) {
                echo "    -> ERROR: " . $e->getMessage() . "\n";
            }
        }
        $addedCount++;
    }
    
    if ($addedCount > 0) {
        echo "  Added $addedCount new partition(s).\n";
    } else {
        echo "  No new partitions needed.\n";
    }
    
    // Archive old partitions (EXCHANGE PARTITION + DROP)
    if ($archiveMode) {
        echo "  Checking for partitions to archive (before year $archiveBeforeYear)...\n";
        $archivedCount = 0;
        
        foreach ($partitions as $part) {
            if (preg_match('/^' . $prefix . '(\d+)$/', $part['PARTITION_NAME'], $matches)) {
                $year = (int)$matches[1];
                if ($year < $archiveBeforeYear && $year > 0) {
                    $partitionName = $part['PARTITION_NAME'];
                    
                    // First, create archive table if not exists
                    $archiveTable = "{$table}_archive";
                    $createArchive = "CREATE TABLE IF NOT EXISTS $archiveTable LIKE $table";
                    
                    echo "  " . ($dryRun ? '[DRY RUN] Would archive' : 'Archiving') . " partition: $partitionName (year $year)\n";
                    
                    if (!$dryRun) {
                        try {
                            // Create archive table with same partitioning
                            $pdo->exec($createArchive);
                            
                            // Also partition the archive table (without subpartitions for simplicity)
                            // Actually, we want to keep it partitioned for query performance
                            $archivePartitionSql = "
                                ALTER TABLE $archiveTable
                                PARTITION BY RANGE ($yearCol)
                                SUBPARTITION BY HASH(tenant_id)
                                SUBPARTITIONS 4
                                (
                                    PARTITION p{$year} VALUES LESS THAN ($nextYear)
                                    (SUBPARTITION p{$year}_s0 ENGINE=InnoDB,
                                     SUBPARTITION p{$year}_s1 ENGINE=InnoDB,
                                     SUBPARTITION p{$year}_s2 ENGINE=InnoDB,
                                     SUBPARTITION p{$year}_s3 ENGINE=InnoDB)
                                )
                            ";
                            // Only partition if not already partitioned
                            $stmt = $pdo->prepare("SELECT PARTITION_METHOD FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1");
                            $stmt->execute([$archiveTable]);
                            $archiveMethod = $stmt->fetchColumn();
                            if (!$archiveMethod || $archiveMethod === 'NULL') {
                                $pdo->exec($archivePartitionSql);
                            }
                            
                            // Exchange partition to archive table (instant, no data copy)
                            $exchangeSql = "ALTER TABLE $table EXCHANGE PARTITION $partitionName WITH TABLE $archiveTable";
                            $pdo->exec($exchangeSql);
                            
                            // Drop the now-empty partition from main table
                            $dropSql = "ALTER TABLE $table DROP PARTITION $partitionName";
                            $pdo->exec($dropSql);
                            
                            echo "    -> SUCCESS (exchanged to $archiveTable, partition dropped)\n";
                        } catch (PDOException $e) {
                            echo "    -> ERROR: " . $e->getMessage() . "\n";
                        }
                    }
                    $archivedCount++;
                }
            }
        }
        
        if ($archivedCount > 0) {
            echo "  Archived $archivedCount partition(s) to ${table}_archive.\n";
        } else {
            echo "  No partitions to archive.\n";
        }
    }
    
    // Rebuild partition metadata (optimize)
    if (!$dryRun && $addedCount > 0) {
        echo "  Running ANALYZE TABLE to update partition statistics...\n";
        try {
            $pdo->exec("ANALYZE TABLE $table");
            echo "    -> SUCCESS\n";
        } catch (PDOException $e) {
            echo "    -> ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

// Summary
if ($dryRun) {
    echo "=== DRY RUN COMPLETE ===\n";
    echo "Run with --execute to apply changes.\n";
    if (!$archiveMode) echo "Add --archive to enable old partition archiving.\n";
} else {
    echo "=== MAINTENANCE COMPLETE ===\n";
}

exit(0);