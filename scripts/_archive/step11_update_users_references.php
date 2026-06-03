<?php
/**
 * Step 11: Update code references for unified users table
 * Update all references to customer, admin_user, agent, associate, employee tables to use unified users
 */

$projectRoot = dirname(__DIR__);
echo "=== STEP 11: UPDATE CODE REFERENCES FOR UNIFIED USERS TABLE ===\n\n";

// Define user table consolidations
$userTableConsolidations = [
    'customers' => 'users',
    'admin_users' => 'users', 
    'agents' => 'users',
    'associates' => 'users',
    'employees' => 'users',
    'customer_profiles' => 'users',
    'customer_preferences' => 'users'
];

echo "📋 Updating user table references throughout codebase...\n\n";

$filesUpdated = 0;
$totalReplacements = 0;

$searchDirs = ['app', 'routes', 'public'];

foreach ($userTableConsolidations as $oldTable => $newTable) {
    echo "🔄 Processing: $oldTable → $newTable\n";
    
    $tableReplacements = 0;
    
    foreach ($searchDirs as $dir) {
        $fullDir = $projectRoot . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($fullDir)) continue;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            if (strpos($file->getPathname(), 'vendor') !== false) continue;
            if (strpos($file->getPathname(), 'node_modules') !== false) continue;
            if (strpos($file->getPathname(), '.git') !== false) continue;
            if (strpos($file->getPathname(), '.deleted') !== false) continue;
            if (strpos($file->getPathname(), '.backup') !== false) continue;
            
            // Only process PHP files
            $ext = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
            if ($ext !== 'php') continue;
            
            $filePath = $file->getPathname();
            $content = file_get_contents($filePath);
            $originalContent = $content;
            
            if ($content === false) continue;
            
            // Apply replacements
            $content = preg_replace("/\b$oldTable\b/i", $newTable, $content);
            
            // Write if changed
            if ($content !== $originalContent) {
                file_put_contents($filePath, $content);
                $filesUpdated++;
                $relativePath = str_replace($projectRoot, '', $filePath);
                echo "  ✓ Updated: $relativePath\n";
                $tableReplacements++;
            }
        }
    }
    
    echo "  → $tableReplacements replacements\n";
    $totalReplacements += $tableReplacements;
    echo "\n";
}

// Check for remaining references
echo "🔍 Checking for remaining user table references...\n\n";

$remainingRefs = 0;
foreach ($userTableConsolidations as $oldTable => $newTable) {
    $count = 0;
    
    foreach ($searchDirs as $dir) {
        $fullDir = $projectRoot . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($fullDir)) continue;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            if (strpos($file->getPathname(), 'vendor') !== false) continue;
            if (strpos($file->getPathname(), 'node_modules') !== false) continue;
            if (strpos($file->getPathname(), '.git') !== false) continue;
            if (strpos($file->getPathname(), '.deleted') !== false) continue;
            if (strpos($file->getPathname(), '.backup') !== false) continue;
            
            $ext = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
            if ($ext !== 'php') continue;
            
            $content = file_get_contents($file->getPathname());
            if ($content && preg_match("/\b$oldTable\b/i", $content)) {
                $count++;
                if ($count <= 3) {
                    $relativePath = str_replace($projectRoot, '', $file->getPathname());
                    echo "  ⚠️  $oldTable found in: $relativePath\n";
                }
            }
        }
    }
    
    if ($count > 0) {
        echo "\n⚠️  $oldTable: $count remaining references\n";
        $remainingRefs += $count;
    } else {
        echo "✓ $oldTable: No remaining references\n";
    }
}

echo "\n=== STEP 11 COMPLETE ===\n";
echo "Summary:\n";
echo "  Files updated: $filesUpdated\n";
echo "  Total replacements: $totalReplacements\n";
echo "  Remaining references: $remainingRefs\n";

if ($remainingRefs > 0) {
    echo "\n⚠️  Note: Some references may be in comments, strings, or model files.\n";
    echo "   Manual review may be needed for remaining references.\n";
} else {
    echo "\n✓ All user table references successfully updated!\n";
}

echo "\nNEXT: Safely drop old user tables (backup first)\n";
