<?php
/**
 * APS Dream Home - N+1 Query Detection and Fix
 * Detect and fix N+1 query problems in models and controllers
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'n_plus_one_query_fix',
    'potential_n_plus_one_issues' => [],
    'fixes_applied' => [],
    'recommendations' => []
];

echo "⚡ N+1 QUERY DETECTION & FIX\n";
echo "===========================\n\n";

// Function to analyze code for N+1 query patterns
function analyzeForNPlusOne($filePath, &$results) {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    if ($content === false) return false;

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);
    $issues = [];

    // Pattern 1: Loop with relationship access without eager loading
    // Look for foreach loops accessing relationships
    preg_match_all('/foreach\s*\(\s*\$(\w+)\s+as\s+\$(\w+)\s*\)\s*\{.*?\$\2->(\w+)[^;]*;/s', $content, $loopMatches);

    foreach ($loopMatches[3] as $index => $relationship) {
        $variable = $loopMatches[2][$index];
        $collection = $loopMatches[1][$index];

        // Check if this relationship is accessed without prior eager loading
        $eagerLoadPattern = '/with\s*\(\s*[\'"]' . preg_quote($relationship) . '[\'"]\s*\)/';
        $loadPattern = '/load\s*\(\s*[\'"]' . preg_quote($relationship) . '[\'"]\s*\)/';

        if (!preg_match($eagerLoadPattern, $content) && !preg_match($loadPattern, $content)) {
            $issues[] = [
                'type' => 'potential_n_plus_one',
                'description' => "Potential N+1 query: accessing \${$variable}->{$relationship} in loop without eager loading",
                'relationship' => $relationship,
                'collection_variable' => $collection,
                'line_context' => "foreach (\${$collection} as \${$variable}) { \${$variable}->{$relationship} }"
            ];
        }
    }

    // Pattern 2: Collection methods that might cause N+1
    // Look for map, each, filter with relationship access
    $collectionMethods = ['map', 'each', 'filter', 'first', 'find'];
    foreach ($collectionMethods as $method) {
        preg_match_all('/\$(\w+)->' . preg_quote($method) . '\s*\([^}]*?->(\w+)[^}]*\}/s', $content, $methodMatches);

        foreach ($methodMatches[2] as $index => $relationship) {
            $variable = $methodMatches[1][$index];

            $eagerLoadPattern = '/with\s*\(\s*[\'"]' . preg_quote($relationship) . '[\'"]\s*\)/';
            $loadPattern = '/load\s*\(\s*[\'"]' . preg_quote($relationship) . '[\'"]\s*\)/';

            if (!preg_match($eagerLoadPattern, $content) && !preg_match($loadPattern, $content)) {
                $issues[] = [
                    'type' => 'potential_n_plus_one',
                    'description' => "Potential N+1 query: accessing relationship in {$method}() method without eager loading",
                    'relationship' => $relationship,
                    'collection_variable' => $variable,
                    'line_context' => "\${$variable}->{$method}(... ->{$relationship} ...)"
                ];
            }
        }
    }

    // Pattern 3: Direct relationship access in views (potential lazy loading)
    preg_match_all('/\$(\w+)->(\w+)[^;]*;/', $content, $accessMatches);

    foreach ($accessMatches[2] as $index => $relationship) {
        $variable = $accessMatches[1][$index];

        // Skip if it's a method call or already handled
        if (in_array($relationship, ['save', 'update', 'delete', 'create', 'find', 'where', 'get', 'first'])) {
            continue;
        }

        // Check if this looks like a relationship access
        if (!preg_match('/with\s*\(\s*[\'"]' . preg_quote($relationship) . '[\'"]\s*\)/', $content)) {
            $issues[] = [
                'type' => 'potential_lazy_loading',
                'description' => "Potential lazy loading: \${$variable}->{$relationship} accessed without eager loading",
                'relationship' => $relationship,
                'variable' => $variable,
                'line_context' => "\${$variable}->{$relationship}"
            ];
        }
    }

    if (!empty($issues)) {
        $results['potential_n_plus_one_issues'][] = [
            'file' => $relativePath,
            'issues' => $issues
        ];
        return true;
    }

    return false;
}

// Function to fix N+1 query issues
function fixNPlusOneIssues($filePath, $issues, &$results) {
    $content = file_get_contents($filePath);
    if ($content === false) return false;

    $relativePath = str_replace(dirname(__FILE__) . '/', '', $filePath);
    $originalContent = $content;
    $fixesApplied = 0;

    // Group issues by collection variable and relationships
    $fixGroups = [];
    foreach ($issues as $issue) {
        $key = $issue['collection_variable'] ?? $issue['variable'];
        if (!isset($fixGroups[$key])) {
            $fixGroups[$key] = [];
        }
        $fixGroups[$key][] = $issue['relationship'];
    }

    // Apply fixes for each group
    foreach ($fixGroups as $variable => $relationships) {
        $uniqueRelationships = array_unique($relationships);

        // Look for the query builder pattern to add with() clause
        $queryPatterns = [
            "/(\\$query\s*=\\s*)?\\\$" . preg_quote($variable) . '\s*=\s*([A-Z]\w*::(?:where|find|all|get|select))/',
            "/(\\$query\s*=\\s*)?\\\$" . preg_quote($variable) . '\s*=\s*\$this->([a-zA-Z_]\w*)\s*\(/',
            "/(\\$query\s*=\\s*)?\\\$" . preg_quote($variable) . '\s*=\s*([a-zA-Z_]\w*::query)\s*\(\s*\)/'
        ];

        foreach ($queryPatterns as $pattern) {
            if (preg_match($pattern, $content, $match)) {
                // Check if with() is already present
                $withPattern = '/->with\s*\(/';
                if (!preg_match($withPattern, $content)) {
                    // Add with() clause after the query
                    $relationshipsStr = "['" . implode("', '", $uniqueRelationships) . "']";
                    $replacement = $match[0] . "->with({$relationshipsStr})";

                    $content = preg_replace($pattern, $replacement, $content, 1, $count);
                    if ($count > 0) {
                        $fixesApplied++;
                        $results['fixes_applied'][] = [
                            'file' => $relativePath,
                            'type' => 'added_eager_loading',
                            'relationships' => $uniqueRelationships,
                            'variable' => $variable
                        ];
                        break;
                    }
                } else {
                    // Update existing with() clause
                    $content = preg_replace_callback(
                        $withPattern,
                        function($match) use ($uniqueRelationships) {
                            $existingWith = $match[0];
                            $relationshipsStr = "['" . implode("', '", $uniqueRelationships) . "']";
                            return $existingWith . "\n        ->with({$relationshipsStr})";
                        },
                        $content,
                        1,
                        $count
                    );
                    if ($count > 0) {
                        $fixesApplied++;
                        $results['fixes_applied'][] = [
                            'file' => $relativePath,
                            'type' => 'updated_eager_loading',
                            'relationships' => $uniqueRelationships,
                            'variable' => $variable
                        ];
                    }
                }
            }
        }
    }

    // Save backup and apply changes
    if ($content !== $originalContent && $fixesApplied > 0) {
        $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
        if (copy($filePath, $backupPath)) {
            if (file_put_contents($filePath, $content) !== false) {
                return true;
            }
        }
    }

    return false;
}

// Scan PHP files for N+1 query issues
echo "🔍 Scanning for N+1 Query Issues\n";
echo "===============================\n";

$scanDirs = ['app/Http/Controllers', 'app/Models', 'app/Services', 'routes'];
$filesWithIssues = 0;
$fixesApplied = 0;

foreach ($scanDirs as $dir) {
    $fullDir = $projectRoot . '/' . $dir;
    if (is_dir($fullDir)) {
        $files = glob($fullDir . '/**/*.php');
        foreach ($files as $file) {
            $relativePath = str_replace($projectRoot . '/', '', $file);

            if (analyzeForNPlusOne($file, $results)) {
                $filesWithIssues++;
                echo "🐌 Potential N+1 issues found in: {$relativePath}\n";

                // Get the issues for this file
                $fileIssues = end($results['potential_n_plus_one_issues'])['issues'];

                // Attempt to apply fixes
                if (fixNPlusOneIssues($file, $fileIssues, $results)) {
                    $fixesApplied++;
                    echo "✅ Fixes applied to: {$relativePath}\n";
                } else {
                    echo "❌ Could not auto-fix: {$relativePath}\n";
                }
            }
        }
    }
}

// Generate summary
echo "\n📊 N+1 Query Analysis Summary\n";
echo "=============================\n";
echo "🐌 Files with potential N+1 issues: {$filesWithIssues}\n";
echo "✅ Files with auto-fixes applied: {$fixesApplied}\n";

if (!empty($results['fixes_applied'])) {
    echo "\n🔧 Fixes Applied\n";
    echo "===============\n";
    foreach ($results['fixes_applied'] as $fix) {
        echo "📁 {$fix['file']}\n";
        echo "   📊 Type: {$fix['type']}\n";
        echo "   🔗 Relationships: " . implode(', ', $fix['relationships']) . "\n";
        echo "   📝 Variable: {$fix['variable']}\n\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Always use eager loading (with()) for relationships in loops\n";
echo "• Use load() for relationships after initial query\n";
echo "• Consider using select() to limit columns when possible\n";
echo "• Use pagination for large datasets\n";
echo "• Monitor database queries in development\n";
echo "• 🔄 Next: Add missing database indexes\n";

$results['summary'] = [
    'files_with_issues' => $filesWithIssues,
    'auto_fixes_applied' => $fixesApplied,
    'manual_review_needed' => $filesWithIssues - $fixesApplied
];

// Save results
$resultsFile = $projectRoot . '/n_plus_one_query_fixes.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ N+1 query detection and fix completed!\n";

?>
