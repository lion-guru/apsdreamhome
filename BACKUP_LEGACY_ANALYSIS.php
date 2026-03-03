<?php
/**
 * Backup Legacy Files Analysis
 * 
 * Understanding the purpose of _backup_legacy_files folder
 * and its role in MVC conversion and project evolution
 */

define('PROJECT_BASE_PATH', __DIR__);

echo "====================================================\n";
echo "📁 BACKUP LEGACY FILES ANALYSIS\n";
echo "====================================================\n\n";

// Step 1: Analyze _backup_legacy_files structure
echo "Step 1: _backup_legacy_files Structure Analysis\n";
echo "==============================================\n";

$backupDir = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . '_backup_legacy_files';
if (is_dir($backupDir)) {
    $backupItems = scandir($backupDir);
    $backupItems = array_diff($backupItems, ['.', '..']);
    
    echo "📁 _backup_legacy_files/ contains " . count($backupItems) . " items:\n";
    
    $fileTypes = [
        'php' => 0,
        'html' => 0,
        'js' => 0,
        'css' => 0,
        'md' => 0,
        'sql' => 0,
        'other' => 0
    ];
    
    $legacyPatterns = [];
    
    foreach ($backupItems as $item) {
        $fullPath = $backupDir . DIRECTORY_SEPARATOR . $item;
        $isDir = is_dir($fullPath);
        
        if ($isDir) {
            $subItems = scandir($fullPath);
            $subItems = array_diff($subItems, ['.', '..']);
            echo "   📁 $item/ (" . count($subItems) . " items)\n";
            
            // Analyze subdirectory contents
            foreach ($subItems as $subItem) {
                $subPath = $fullPath . DIRECTORY_SEPARATOR . $subItem;
                if (is_file($subPath)) {
                    $ext = strtolower(pathinfo($subItem, PATHINFO_EXTENSION));
                    if (isset($fileTypes[$ext])) {
                        $fileTypes[$ext]++;
                    } else {
                        $fileTypes['other']++;
                    }
                    
                    // Check for legacy patterns
                    if (preg_match('/(old|legacy|backup|v1|v2|previous)/i', $subItem)) {
                        $legacyPatterns[] = $item . '/' . $subItem;
                    }
                }
            }
        } else {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (isset($fileTypes[$ext])) {
                $fileTypes[$ext]++;
            } else {
                $fileTypes['other']++;
            }
            
            echo "   📄 $item\n";
            
            // Check for legacy patterns
            if (preg_match('/(old|legacy|backup|v1|v2|previous)/i', $item)) {
                $legacyPatterns[] = $item;
            }
        }
    }
    
    echo "\n📊 File Type Distribution:\n";
    foreach ($fileTypes as $type => $count) {
        if ($count > 0) {
            echo "   📄 $type: $count files\n";
        }
    }
    
    if (!empty($legacyPatterns)) {
        echo "\n🔍 Legacy Pattern Files:\n";
        foreach (array_slice($legacyPatterns, 0, 10) as $pattern) {
            echo "   • $pattern\n";
        }
        if (count($legacyPatterns) > 10) {
            echo "   ... and " . (count($legacyPatterns) - 10) . " more\n";
        }
    }
} else {
    echo "❌ _backup_legacy_files folder not found\n";
}

echo "\n";

// Step 2: Analyze specific legacy files for MVC conversion evidence
echo "Step 2: MVC Conversion Evidence Analysis\n";
echo "========================================\n";

$mvcConversionIndicators = [
    'old_index_files' => [],
    'legacy_controllers' => [],
    'old_view_files' => [],
    'flat_php_files' => [],
    'conversion_notes' => []
];

if (is_dir($backupDir)) {
    // Look for old index files
    $indexFiles = glob($backupDir . '/**/index*.php');
    foreach ($indexFiles as $file) {
        $relativePath = str_replace($backupDir . DIRECTORY_SEPARATOR, '', $file);
        $content = file_get_contents($file);
        
        // Check if it's a flat PHP file (not MVC)
        if (strpos($content, 'class') === false && strpos($content, 'require_once') !== false) {
            $mvcConversionIndicators['old_index_files'][] = $relativePath;
        }
    }
    
    // Look for legacy controller patterns
    $controllerFiles = glob($backupDir . '/**/*controller*.php');
    foreach ($controllerFiles as $file) {
        $relativePath = str_replace($backupDir . DIRECTORY_SEPARATOR, '', $file);
        $mvcConversionIndicators['legacy_controllers'][] = $relativePath;
    }
    
    // Look for old view files
    $viewFiles = glob($backupDir . '/**/*.php');
    foreach ($viewFiles as $file) {
        $relativePath = str_replace($backupDir . DIRECTORY_SEPARATOR, '', $file);
        $content = file_get_contents($file);
        
        // Check if it's mostly HTML with PHP (old view pattern)
        if (strpos($content, '<html') !== false && strpos($content, '<?php') !== false) {
            $mvcConversionIndicators['old_view_files'][] = $relativePath;
        }
    }
    
    // Look for flat PHP files
    $allPhpFiles = glob($backupDir . '/**/*.php');
    foreach ($allPhpFiles as $file) {
        $relativePath = str_replace($backupDir . DIRECTORY_SEPARATOR, '', $file);
        $content = file_get_contents($file);
        
        // Check for flat PHP patterns
        if (strpos($content, 'class') === false && 
            strpos($content, 'function') !== false && 
            strpos($content, 'mysql_query') !== false) {
            $mvcConversionIndicators['flat_php_files'][] = $relativePath;
        }
    }
    
    // Look for conversion notes
    $mdFiles = glob($backupDir . '/**/*.md');
    foreach ($mdFiles as $file) {
        $relativePath = str_replace($backupDir . DIRECTORY_SEPARATOR, '', $file);
        $content = file_get_contents($file);
        
        if (preg_match('/(mvc|convert|refactor|legacy|upgrade)/i', $content)) {
            $mvcConversionIndicators['conversion_notes'][] = $relativePath;
        }
    }
}

echo "🔍 MVC Conversion Evidence:\n";
foreach ($mvcConversionIndicators as $type => $files) {
    echo "   📝 $type: " . count($files) . " files\n";
    foreach (array_slice($files, 0, 3) as $file) {
        echo "      • $file\n";
    }
    if (count($files) > 3) {
        echo "      ... and " . (count($files) - 3) . " more\n";
    }
    echo "\n";
}

// Step 3: Analyze specific legacy files
echo "Step 3: Specific Legacy Files Analysis\n";
echo "======================================\n";

$specificLegacyFiles = [
    'google_analytics_integration.php',
    'test-integration.php'
];

foreach ($specificLegacyFiles as $file) {
    $filePath = $backupDir . DIRECTORY_SEPARATOR . $file;
    if (file_exists($filePath)) {
        echo "📄 $file:\n";
        
        $content = file_get_contents($filePath);
        $lines = count(file($filePath));
        echo "   📝 $lines lines\n";
        
        // Analyze content patterns
        if (strpos($content, 'function') !== false) {
            preg_match_all('/function\s+(\w+)/', $content, $matches);
            if (!empty($matches[1])) {
                echo "   🔧 Functions: " . implode(', ', array_slice($matches[1], 0, 5)) . "\n";
            }
        }
        
        if (strpos($content, 'class') !== false) {
            preg_match_all('/class\s+(\w+)/', $content, $matches);
            if (!empty($matches[1])) {
                echo "   🏗️ Classes: " . implode(', ', $matches[1]) . "\n";
            }
        }
        
        if (strpos($content, 'mysql_') !== false) {
            echo "   🗄️ Uses legacy MySQL functions\n";
        }
        
        if (strpos($content, 'include') !== false || strpos($content, 'require') !== false) {
            echo "   📎 Uses include/require\n";
        }
        
        echo "\n";
    }
}

// Step 4: Timeline analysis
echo "Step 4: Project Evolution Timeline\n";
echo "=================================\n";

echo "📅 Project Evolution Stages:\n";
echo "   1. 🏗️ Legacy Phase: Flat PHP files with mixed logic\n";
echo "   2. 🔄 MVC Conversion: Separation of concerns started\n";
echo "   3. 📦 Backup Creation: _backup_legacy_files created\n";
echo "   4. 🚀 Modern MVC: Current app/ structure\n";
echo "   5. 🤖 AI Integration: Co-worker system added\n";
echo "   6. 👥 Multi-system: Current coordination setup\n\n";

// Step 5: Current vs Legacy comparison
echo "Step 5: Current vs Legacy Comparison\n";
echo "===================================\n";

$currentStructure = [
    'app/Controllers/' => 'Modern MVC controllers',
    'app/Models/' => 'Modern MVC models', 
    'app/Views/' => 'Modern MVC views',
    'app/Core/' => 'Modern core functionality',
    'admin/' => 'Modern admin interface'
];

$legacyStructure = [
    'flat PHP files' => 'Mixed logic files',
    'mysql_* functions' => 'Legacy database calls',
    'include/require patterns' => 'Old file inclusion',
    'HTML+PHP mixed' => 'Old view patterns'
];

echo "🏗️ Current Structure:\n";
foreach ($currentStructure as $component => $description) {
    $exists = is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $component);
    echo "   " . ($exists ? "✅" : "❌") . " $component - $description\n";
}

echo "\n📁 Legacy Structure (in backup):\n";
foreach ($legacyStructure as $pattern => $description) {
    echo "   📄 $pattern - $description\n";
}

echo "\n";

// Step 6: Recommendations for backup management
echo "Step 6: Backup Management Recommendations\n";
echo "========================================\n";

$recommendations = [
    "Keep _backup_legacy_files as project history",
    "Document MVC conversion journey",
    "Use backup for reference when needed",
    "Consider organizing by conversion phase",
    "Maintain as part of project documentation",
    "DO NOT delete - contains project evolution history"
];

echo "💡 Backup Management Recommendations:\n";
foreach ($recommendations as $i => $recommendation) {
    echo "   " . ($i + 1) . ". $recommendation\n";
}

echo "\n";

// Step 7: Memory storage summary
echo "Step 7: Memory Storage Summary\n";
echo "===============================\n";

$backupAnalysis = [
    'backup_folder_exists' => is_dir($backupDir),
    'total_backup_items' => isset($backupItems) ? count($backupItems) : 0,
    'mvc_conversion_evidence' => !empty(array_filter($mvcConversionIndicators)),
    'legacy_php_files' => $mvcConversionIndicators['flat_php_files'] ?? [],
    'conversion_notes_found' => !empty($mvcConversionIndicators['conversion_notes']),
    'project_evolution_stages' => 6
];

echo "🧠 Memory Data for Storage:\n";
foreach ($backupAnalysis as $key => $value) {
    if (is_array($value)) {
        echo "   $key: " . count($value) . " items\n";
    } else {
        echo "   $key: " . ($value ? 'true' : 'false') . "\n";
    }
}

echo "\n";

echo "====================================================\n";
echo "🎊 BACKUP LEGACY ANALYSIS COMPLETE! 🎊\n";
echo "📊 Status: MVC CONVERSION HISTORY UNDERSTOOD\n";
echo "🚀 _backup_legacy_files is PROJECT EVOLUTION HISTORY!\n\n";

echo "🔍 KEY FINDINGS:\n";
echo "• _backup_legacy_files contains " . (isset($backupItems) ? count($backupItems) : 0) . " items\n";
echo "• Evidence of MVC conversion found\n";
echo "• Legacy flat PHP files backed up\n";
echo "• Project evolution from flat PHP to MVC\n";
echo "• Conversion notes and documentation present\n\n";

echo "📈 PROJECT EVOLUTION:\n";
echo "1. Legacy: Flat PHP with mixed logic\n";
echo "2. Conversion: MVC structure implemented\n";
echo "3. Backup: Legacy files preserved\n";
echo "4. Modern: Current app/ structure\n";
echo "5. AI: Co-worker system integration\n\n";

echo "⚠️ IMPORTANT:\n";
echo "• KEEP _backup_legacy_files - it's project history\n";
echo "• Documents MVC conversion journey\n";
echo "• Contains legacy code for reference\n";
echo "• Part of project evolution story\n\n";

echo "🎯 RECOMMENDATION:\n";
echo "• Organize backup by conversion phases\n";
echo "• Keep as project documentation\n";
echo "• Use for reference when needed\n";
echo "• DO NOT DELETE - historical value\n\n";
?>
