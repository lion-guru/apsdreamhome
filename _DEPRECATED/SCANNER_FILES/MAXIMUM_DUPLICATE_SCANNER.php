<?php
/**
 * APS Dream Home - Maximum Level Deep Duplicate Scanner
 * Ultra-deep scan for any type of duplicates in the entire project
 */

echo "🔍 APS DREAM HOME - MAXIMUM LEVEL DEEP DUPLICATE SCANNER\n";
echo "========================================================\n\n";

// Initialize comprehensive results
$scanResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_files_scanned' => 0,
    'exact_duplicates' => 0,
    'near_duplicates' => 0,
    'partial_duplicates' => 0,
    'structural_duplicates' => 0,
    'functional_duplicates' => 0,
    'duplicate_groups' => [],
    'scan_details' => []
];

echo "🔍 STARTING MAXIMUM LEVEL DEEP SCAN...\n";

// Get all PHP files
$projectRoot = __DIR__ . '/..';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot));
$allFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $relativePath = str_replace($projectRoot . '/', '', $filePath);
        
        // Skip vendor and node_modules
        if (strpos($relativePath, 'vendor/') !== false || strpos($relativePath, 'node_modules/') !== false) {
            continue;
        }
        
        $content = file_get_contents($filePath);
        
        $allFiles[] = [
            'path' => $relativePath,
            'absolute_path' => $filePath,
            'size' => filesize($filePath),
            'lines' => substr_count($content, "\n"),
            'content' => $content,
            'content_hash' => md5($content),
            'normalized_content' => normalizeContent($content),
            'functions' => extractFunctions($content),
            'classes' => extractClasses($content),
            'structure' => analyzeStructure($content)
        ];
        
        $scanResults['total_files_scanned']++;
    }
}

echo "   Total PHP files scanned: " . count($allFiles) . "\n\n";

echo "🔍 LEVEL 1: EXACT DUPLICATE ANALYSIS...\n";
// Level 1: Exact duplicates (same content hash)
$contentGroups = [];
foreach ($allFiles as $file) {
    $hash = $file['content_hash'];
    if (!isset($contentGroups[$hash])) {
        $contentGroups[$hash] = [];
    }
    $contentGroups[$hash][] = $file;
}

$exactDuplicates = [];
foreach ($contentGroups as $hash => $files) {
    if (count($files) > 1) {
        $exactDuplicates[] = [
            'type' => 'exact_duplicate',
            'hash' => $hash,
            'files' => $files,
            'count' => count($files),
            'severity' => 'HIGH'
        ];
        $scanResults['exact_duplicates'] += count($files) - 1;
    }
}

echo "   Exact duplicate groups: " . count($exactDuplicates) . "\n";

echo "\n🔍 LEVEL 2: NEAR DUPLICATE ANALYSIS...\n";
// Level 2: Near duplicates (normalized content)
$normalizedGroups = [];
foreach ($allFiles as $file) {
    $normalizedHash = md5($file['normalized_content']);
    if (!isset($normalizedGroups[$normalizedHash])) {
        $normalizedGroups[$normalizedHash] = [];
    }
    $normalizedGroups[$normalizedHash][] = $file;
}

$nearDuplicates = [];
foreach ($normalizedGroups as $hash => $files) {
    if (count($files) > 1) {
        // Skip if already counted as exact duplicates
        $uniqueHashes = array_unique(array_column($files, 'content_hash'));
        if (count($uniqueHashes) > 1) {
            $nearDuplicates[] = [
                'type' => 'near_duplicate',
                'hash' => $hash,
                'files' => $files,
                'count' => count($files),
                'severity' => 'MEDIUM'
            ];
            $scanResults['near_duplicates'] += count($files) - 1;
        }
    }
}

echo "   Near duplicate groups: " . count($nearDuplicates) . "\n";

echo "\n🔍 LEVEL 3: PARTIAL DUPLICATE ANALYSIS...\n";
// Level 3: Partial duplicates (similar functions/classes)
$partialDuplicates = [];
for ($i = 0; $i < count($allFiles); $i++) {
    for ($j = $i + 1; $j < count($allFiles); $j++) {
        $file1 = $allFiles[$i];
        $file2 = $allFiles[$j];
        
        // Skip if already identified as exact/near duplicates
        if ($file1['content_hash'] === $file2['content_hash']) continue;
        
        $similarity = calculateSimilarity($file1['normalized_content'], $file2['normalized_content']);
        
        if ($similarity > 0.8) { // 80% similarity threshold
            $partialDuplicates[] = [
                'type' => 'partial_duplicate',
                'files' => [$file1, $file2],
                'similarity' => round($similarity * 100, 2),
                'severity' => 'MEDIUM'
            ];
            $scanResults['partial_duplicates']++;
        }
    }
}

echo "   Partial duplicate groups: " . count($partialDuplicates) . "\n";

echo "\n🔍 LEVEL 4: STRUCTURAL DUPLICATE ANALYSIS...\n";
// Level 4: Structural duplicates (same class/function names)
$structuralGroups = [];
foreach ($allFiles as $file) {
    $structureKey = generateStructureKey($file);
    if (!isset($structuralGroups[$structureKey])) {
        $structuralGroups[$structureKey] = [];
    }
    $structuralGroups[$structureKey][] = $file;
}

$structuralDuplicates = [];
foreach ($structuralGroups as $key => $files) {
    if (count($files) > 1) {
        // Skip if already identified as higher-level duplicates
        $alreadyIdentified = false;
        foreach ($files as $file) {
            foreach (array_merge($exactDuplicates, $nearDuplicates, $partialDuplicates) as $dup) {
                if (in_array($file['path'], array_column($dup['files'], 'path'))) {
                    $alreadyIdentified = true;
                    break 2;
                }
            }
        }
        
        if (!$alreadyIdentified) {
            $structuralDuplicates[] = [
                'type' => 'structural_duplicate',
                'structure_key' => $key,
                'files' => $files,
                'count' => count($files),
                'severity' => 'LOW'
            ];
            $scanResults['structural_duplicates'] += count($files) - 1;
        }
    }
}

echo "   Structural duplicate groups: " . count($structuralDuplicates) . "\n";

echo "\n🔍 LEVEL 5: FUNCTIONAL DUPLICATE ANALYSIS...\n";
// Level 5: Functional duplicates (same functionality)
$functionalDuplicates = [];
$functionSignatures = [];

foreach ($allFiles as $file) {
    foreach ($file['functions'] as $function) {
        $signature = $function['name'] . '(' . implode(',', $function['params']) . ')';
        if (!isset($functionSignatures[$signature])) {
            $functionSignatures[$signature] = [];
        }
        $functionSignatures[$signature][] = [
            'file' => $file,
            'function' => $function
        ];
    }
}

foreach ($functionSignatures as $signature => $functions) {
    if (count($functions) > 1) {
        // Check if these files are already identified as duplicates
        $alreadyIdentified = false;
        foreach ($functions as $func) {
            foreach (array_merge($exactDuplicates, $nearDuplicates, $partialDuplicates, $structuralDuplicates) as $dup) {
                if (in_array($func['file']['path'], array_column($dup['files'], 'path'))) {
                    $alreadyIdentified = true;
                    break 2;
                }
            }
        }
        
        if (!$alreadyIdentified) {
            $functionalDuplicates[] = [
                'type' => 'functional_duplicate',
                'signature' => $signature,
                'functions' => $functions,
                'count' => count($functions),
                'severity' => 'LOW'
            ];
            $scanResults['functional_duplicates'] += count($functions) - 1;
        }
    }
}

echo "   Functional duplicate groups: " . count($functionalDuplicates) . "\n";

// Combine all duplicate groups
$allDuplicateGroups = array_merge($exactDuplicates, $nearDuplicates, $partialDuplicates, $structuralDuplicates, $functionalDuplicates);
$scanResults['duplicate_groups'] = $allDuplicateGroups;

echo "\n📊 MAXIMUM LEVEL SCAN RESULTS:\n";
echo "==============================\n";
echo "📁 Total Files Scanned: " . $scanResults['total_files_scanned'] . "\n";
echo "🔄 Exact Duplicates: " . $scanResults['exact_duplicates'] . "\n";
echo "📝 Near Duplicates: " . $scanResults['near_duplicates'] . "\n";
echo "📋 Partial Duplicates: " . $scanResults['partial_duplicates'] . "\n";
echo "🏗️ Structural Duplicates: " . $scanResults['structural_duplicates'] . "\n";
echo "⚙️ Functional Duplicates: " . $scanResults['functional_duplicates'] . "\n";
echo "📊 Total Duplicate Groups: " . count($allDuplicateGroups) . "\n";

// Display detailed results
if (!empty($allDuplicateGroups)) {
    echo "\n⚠️ DUPLICATES FOUND - DETAILED ANALYSIS:\n";
    echo "=====================================\n";
    
    $groupCount = 0;
    foreach ($allDuplicateGroups as $group) {
        if ($groupCount >= 20) { // Limit display to first 20 groups
            echo "   ... (showing first 20 groups, " . (count($allDuplicateGroups) - 20) . " more)\n";
            break;
        }
        
        echo "\n📁 " . strtoupper(str_replace('_', ' ', $group['type'])) . " (Severity: " . $group['severity'] . ")\n";
        
        if (isset($group['similarity'])) {
            echo "   Similarity: " . $group['similarity'] . "%\n";
        }
        
        echo "   Files (" . $group['count'] . "):\n";
        foreach ($group['files'] as $file) {
            echo "   - " . $file['path'] . " (" . $file['lines'] . " lines, " . $file['size'] . " bytes)\n";
        }
        
        $groupCount++;
    }
} else {
    echo "\n✅ NO DUPLICATES FOUND AT ANY LEVEL!\n";
    echo "=====================================\n";
    echo "   Project is completely duplicate-free.\n";
}

// Generate comprehensive report
$comprehensiveReport = [
    'timestamp' => $scanResults['timestamp'],
    'scan_summary' => [
        'total_files_scanned' => $scanResults['total_files_scanned'],
        'exact_duplicates' => $scanResults['exact_duplicates'],
        'near_duplicates' => $scanResults['near_duplicates'],
        'partial_duplicates' => $scanResults['partial_duplicates'],
        'structural_duplicates' => $scanResults['structural_duplicates'],
        'functional_duplicates' => $scanResults['functional_duplicates'],
        'total_duplicate_groups' => count($allDuplicateGroups),
        'project_clean' => empty($allDuplicateGroups)
    ],
    'duplicate_analysis' => [
        'exact_duplicates' => $exactDuplicates,
        'near_duplicates' => $nearDuplicates,
        'partial_duplicates' => $partialDuplicates,
        'structural_duplicates' => $structuralDuplicates,
        'functional_duplicates' => $functionalDuplicates
    ],
    'recommendations' => []
];

if (!empty($exactDuplicates)) {
    $comprehensiveReport['recommendations'][] = 'URGENT: Delete exact duplicate files immediately';
}

if (!empty($nearDuplicates)) {
    $comprehensiveReport['recommendations'][] = 'HIGH: Review and consolidate near duplicate files';
}

if (!empty($partialDuplicates)) {
    $comprehensiveReport['recommendations'][] = 'MEDIUM: Analyze partial duplicates for code reuse';
}

if (!empty($structuralDuplicates)) {
    $comprehensiveReport['recommendations'][] = 'LOW: Consider refactoring structural duplicates';
}

if (!empty($functionalDuplicates)) {
    $comprehensiveReport['recommendations'][] = 'LOW: Review functional duplicates for consolidation';
}

if (empty($allDuplicateGroups)) {
    $comprehensiveReport['recommendations'][] = 'EXCELLENT: Project is completely duplicate-free';
}

file_put_contents(__DIR__ . '/../maximum_level_duplicate_scan.json', json_encode($comprehensiveReport, JSON_PRETTY_PRINT));

echo "\n✅ MAXIMUM LEVEL DEEP SCAN COMPLETE!\n";
echo "📄 Report saved to: maximum_level_duplicate_scan.json\n";

if (empty($allDuplicateGroups)) {
    echo "\n🎉 STATUS: PERFECT - No duplicates found at any level!\n";
} else {
    echo "\n⚠️ STATUS: ACTION NEEDED - " . count($allDuplicateGroups) . " duplicate groups found!\n";
}

echo "\n🔍 SCAN LEVELS COMPLETED:\n";
echo "========================\n";
echo "✅ Level 1: Exact duplicates (100% match)\n";
echo "✅ Level 2: Near duplicates (normalized match)\n";
echo "✅ Level 3: Partial duplicates (80%+ similarity)\n";
echo "✅ Level 4: Structural duplicates (same structure)\n";
echo "✅ Level 5: Functional duplicates (same functions)\n";

// Helper functions
function normalizeContent($content) {
    // Remove comments, whitespace, and normalize for comparison
    $content = preg_replace('/\/\*[\s\S]*?\*\//', '', $content); // Remove block comments
    $content = preg_replace('/\/\/.*$/m', '', $content); // Remove line comments
    $content = preg_replace('/\s+/', ' ', $content); // Normalize whitespace
    $content = strtolower(trim($content)); // Lowercase and trim
    return $content;
}

function extractFunctions($content) {
    $functions = [];
    preg_match_all('/function\s+(\w+)\s*\(([^)]*)\)/', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $params = array_map('trim', explode(',', $match[2]));
        $params = array_map(function($p) { return preg_replace('/\s.*$/', '', $p); }, $params);
        $functions[] = [
            'name' => $match[1],
            'params' => $params
        ];
    }
    return $functions;
}

function extractClasses($content) {
    $classes = [];
    preg_match_all('/class\s+(\w+)/', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $classes[] = $match[1];
    }
    return $classes;
}

function analyzeStructure($content) {
    return [
        'functions_count' => count(extractFunctions($content)),
        'classes_count' => count(extractClasses($content)),
        'lines' => substr_count($content, "\n")
    ];
}

function calculateSimilarity($str1, $str2) {
    $len1 = strlen($str1);
    $len2 = strlen($str2);
    $maxLen = max($len1, $len2);
    
    if ($maxLen == 0) return 0;
    
    similar_text($str1, $str2, $percent);
    return $percent / 100;
}

function generateStructureKey($file) {
    $structure = $file['structure'];
    return $structure['classes_count'] . '_' . $structure['functions_count'] . '_' . $structure['lines'];
}

?>
