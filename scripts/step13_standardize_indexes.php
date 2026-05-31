<?php
/**
 * Step 13: Standardize 28 non-standard index names
 * Convert all non-standard index names to idx_ prefix format
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 13: STANDARDIZE NON-STANDARD INDEX NAMES ===\n\n";

try {
    // Focus on core tables that had index naming issues
    $coreTables = ['users', 'leads', 'properties', 'activities'];
    
    $totalIndexesRenamed = 0;
    $indexesSkipped = [];
    
    foreach ($coreTables as $table) {
        echo "📋 Processing: $table\n";
        
        try {
            // Get current indexes
            $indexes = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            
            $indexGroups = [];
            foreach ($indexes as $index) {
                $indexName = $index['Key_name'];
                $columnName = $index['Column_name'];
                
                if ($indexName === 'PRIMARY') continue;
                
                // Group by column
                $indexGroups[$columnName][] = $indexName;
            }
            
            echo "  Found " . count($indexGroups) . " indexed columns\n";
            
            foreach ($indexGroups as $column => $names) {
                // Sort by name (keep most recently created as primary if multiple)
                sort($names);
                
                $currentName = end($names); // Get last one (most recent)
                
                // Check if it needs standardization
                if (!preg_match('/^idx_/', $currentName)) {
                    $newName = 'idx_' . $column;
                    
                    echo "  - '$currentName' (on $column) → '$newName'\n";
                    
                    try {
                        // Drop old index
                        $pdo->exec("ALTER TABLE `$table` DROP INDEX `$currentName`");
                        
                        // Create new index with standard name
                        if (count($names) > 1) {
                            // If it was a composite index, need to handle specially
                            $indexData = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name = '$currentName'")->fetchAll(PDO::FETCH_ASSOC);
                            if (!empty($indexData)) {
                                // For now, skip complex composite indexes
                                echo "    ⚠️  Skipping composite/index (manual review needed)\n";
                                $indexesSkipped[] = "$table.$currentName";
                            }
                        } else {
                            $pdo->exec("ALTER TABLE `$table` ADD INDEX $newName ($column)");
                            echo "    ✓ Created new standard index\n";
                            $totalIndexesRenamed++;
                        }
                    } catch (Exception $e) {
                        echo "    ⚠️  Could not rename: {$e->getMessage()}\n";
                        $indexesSkipped[] = "$table.$currentName";
                    }
                }
            }
            
        } catch (Exception $e) {
            echo "  ⚠️  Error processing $table: {$e->getMessage()}\n";
        }
        
        echo "\n";
    }
    
    echo "=== STEP 13 COMPLETE ===\n";
    echo "Summary:\n";
    echo "  Indexes renamed: $totalIndexesRenamed\n";
    echo "  Skipped (manual review): " . count($indexesSkipped) . "\n";
    echo "\n✓ Index naming standardized where possible\n";
    echo "✅ Composite indexes require manual review\n";
    
    if (!empty($indexesSkipped)) {
        echo "\nSkipped indexes (manual review required):\n";
        foreach ($indexesSkipped as $skip) {
            echo "  - $skip\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
