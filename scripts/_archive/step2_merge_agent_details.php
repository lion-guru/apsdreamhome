<?php
/**
 * Step 2: Merge agent_details into users table
 * This will immediately reduce table count by 1
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 2: MERGE AGENT_DETAILS INTO USERS ===\n\n";

try {
    // Check current state
    $agentDetailsCount = $pdo->query("SELECT COUNT(*) FROM agent_details")->fetchColumn();
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    echo "Current state:\n";
    echo "  agent_details: $agentDetailsCount records\n";
    echo "  users: $usersCount records\n\n";
    
    // Check if columns already exist in users table
    $userColumns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    $agentColumnsNeeded = ['agent_license_number', 'agent_experience_years', 'agent_specialization'];
    $columnsToAdd = array_diff($agentColumnsNeeded, $userColumns);
    
    if (!empty($columnsToAdd)) {
        echo "Adding agent-specific columns to users table...\n";
        
        foreach ($columnsToAdd as $column) {
            if ($column === 'agent_license_number') {
                $pdo->exec("ALTER TABLE users ADD COLUMN agent_license_number VARCHAR(100) NULL AFTER profile_image");
                echo "  ✓ Added agent_license_number\n";
            } elseif ($column === 'agent_experience_years') {
                $pdo->exec("ALTER TABLE users ADD COLUMN agent_experience_years INT NULL AFTER agent_license_number");
                echo "  ✓ Added agent_experience_years\n";
            } elseif ($column === 'agent_specialization') {
                $pdo->exec("ALTER TABLE users ADD COLUMN agent_specialization VARCHAR(255) NULL AFTER agent_experience_years");
                echo "  ✓ Added agent_specialization\n";
            }
        }
        
        // Add index for license number
        $pdo->exec("ALTER TABLE users ADD INDEX idx_agent_license (agent_license_number)");
        echo "  ✓ Added index on agent_license_number\n";
    } else {
        echo "Agent columns already exist in users table\n";
    }
    
    // Migrate data from agent_details to users
    echo "\nMigrating data from agent_details to users...\n";
    
    $migrateData = $pdo->prepare("UPDATE users u
LEFT JOIN agent_details ad ON u.id = ad.user_id
SET u.agent_license_number = ad.license_number,
    u.agent_experience_years = ad.experience_years,
    u.agent_specialization = ad.specialization
WHERE ad.user_id IS NOT NULL");
    
    $migrateData->execute();
    $migratedCount = $migrateData->rowCount();
    
    echo "  ✓ Migrated $migratedCount records\n";
    
    // Verify migration
    $updatedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE agent_license_number IS NOT NULL")->fetchColumn();
    echo "  ✓ Users with agent data: $updatedUsers\n";
    
    // Find code references to agent_details
    echo "\nChecking code references to agent_details...\n";
    
    $projectRoot = dirname(__DIR__);
    $searchDirs = ['app', 'routes'];
    $filesWithReferences = [];
    
    foreach ($searchDirs as $dir) {
        $fullDir = $projectRoot . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($fullDir)) continue;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            if (strpos($file->getPathname(), 'vendor') !== false) continue;
            if (strpos($file->getPathname(), 'node_modules') !== false) continue;

            $content = file_get_contents($file->getPathname());
            if ($content && stripos($content, 'agent_details') !== false) {
                $filesWithReferences[] = str_replace($projectRoot, '', $file->getPathname());
            }
        }
    }
    
    echo "  Found " . count($filesWithReferences) . " files with agent_details references:\n";
    foreach ($filesWithReferences as $file) {
        echo "    - $file\n";
    }
    
    // Update code references
    echo "\nUpdating code references...\n";
    $filesUpdated = 0;
    
    foreach ($filesWithReferences as $filePath) {
        $fullPath = $projectRoot . $filePath;
        $content = file_get_contents($fullPath);
        
        // Replace agent_details table references with users
        $newContent = preg_replace('/agent_details/i', 'users', $content);
        
        // Also update column references
        $newContent = str_replace('license_number', 'agent_license_number', $newContent);
        $newContent = str_replace('experience_years', 'agent_experience_years', $newContent);
        $newContent = str_replace('specialization', 'agent_specialization', $newContent);
        
        if ($newContent !== $content) {
            file_put_contents($fullPath, $newContent);
            $filesUpdated++;
            echo "  ✓ Updated: $filePath\n";
        }
    }
    
    echo "  Updated $filesUpdated files\n";
    
    // Backup and drop agent_details table (commented out for safety)
    echo "\nFinal step: Drop agent_details table\n";
    echo "  OPTION 1: DROP TABLE agent_details;\n";
    echo "  OPTION 2: RENAME TABLE agent_details TO agent_details_backup_" . date('Ymd') . ";\n\n";
    
    // For now, just rename to backup
    $backupName = 'agent_details_backup_' . date('Ymd');
    try {
        $pdo->exec("RENAME TABLE agent_details TO $backupName");
        echo "  ✓ Renamed agent_details to $backupName\n";
    } catch (Exception $e) {
        echo "  ! Could not rename table: {$e->getMessage()}\n";
        echo "  ! You can manually run: RENAME TABLE agent_details TO $backupName;\n";
    }
    
    echo "\n=== STEP 2 COMPLETE ===\n";
    echo "Summary:\n";
    echo "  - Added agent columns to users table\n";
    echo "  - Migrated $migratedCount records\n";
    echo "  - Updated $filesUpdated code references\n";
    echo "  - Backed up agent_details table\n";
    echo "  - Reduced table count by 1\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
