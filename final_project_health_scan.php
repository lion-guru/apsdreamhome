<?php

/**
 * APS Dream Home - Final Project Health Scan
 * Performs a deep scan of the project excluding vendor/library files.
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

$projectRoot = __DIR__;
$scanDirs = [
    'app',
    'config',
    'routes',
    'resources/views',
    'public'
];

$excludeDirs = [
    'vendor',
    'node_modules',
    'storage',
    'tests',
    '.git',
    '.idea',
    '.vscode'
];

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'metrics' => [
        'files_scanned' => 0,
        'issues_found' => 0,
        'security_issues' => 0,
        'performance_issues' => 0,
        'syntax_errors' => 0
    ],
    'issues' => []
];

echo "🔍 Starting Final Project Health Scan...\n";
echo "=======================================\n";

function scanDirectory($dir, &$results, $projectRoot, $excludeDirs)
{
    if (!is_dir($dir)) return;

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $path = $dir . '/' . $file;
        $realPath = realpath($path);

        // Check exclusions
        foreach ($excludeDirs as $exclude) {
            // Check both relative and absolute paths
            if (
                strpos($path, '/' . $exclude . '/') !== false ||
                strpos($path, '\\' . $exclude . '\\') !== false ||
                strpos($realPath, $projectRoot . DIRECTORY_SEPARATOR . $exclude) === 0
            ) {
                continue 2;
            }
        }

        $relativePath = str_replace($projectRoot . '/', '', $path);
        // Normalize slashes for display
        $relativePath = str_replace('\\', '/', $relativePath);

        if (is_dir($path)) {
            scanDirectory($path, $results, $projectRoot, $excludeDirs);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            analyzeFile($path, $relativePath, $results);
        }
    }
}

function analyzeFile($filePath, $relativePath, &$results)
{
    echo ".";
    $results['metrics']['files_scanned']++;
    $content = file_get_contents($filePath);

    // 1. Syntax Check (Lint)
    $output = [];
    $returnVar = 0;
    exec("php -l \"$filePath\"", $output, $returnVar);
    if ($returnVar !== 0) {
        $results['metrics']['issues_found']++;
        $results['metrics']['syntax_errors']++;
        $results['issues'][] = [
            'file' => $relativePath,
            'type' => 'Syntax Error',
            'severity' => 'CRITICAL',
            'message' => trim(implode("\n", $output))
        ];
    }

    // 2. Security Checks
    // Hardcoded secrets
    if (preg_match('/[\'"](api_key|secret|password|token)[\'"]\s*=>\s*[\'"][^\'"]{5,}[\'"]/', $content, $matches)) {
        // Exclude config files which might use getenv
        if (strpos($relativePath, 'config/') === false && strpos($content, 'getenv') === false && strpos($content, 'env(') === false) {
            $results['metrics']['issues_found']++;
            $results['metrics']['security_issues']++;
            $results['issues'][] = [
                'file' => $relativePath,
                'type' => 'Security Risk',
                'severity' => 'HIGH',
                'message' => 'Potential hardcoded secret detected'
            ];
        }
    }

    // XSS Vulnerabilities (basic check)
    if (preg_match('/echo\s+\$_(GET|POST|REQUEST)\[/', $content)) {
        $results['metrics']['issues_found']++;
        $results['metrics']['security_issues']++;
        $results['issues'][] = [
            'file' => $relativePath,
            'type' => 'XSS Vulnerability',
            'severity' => 'HIGH',
            'message' => 'Direct echo of user input detected'
        ];
    }

    // 3. Performance Checks (N+1 Query)
    // Loop with relationship access
    if (preg_match_all('/foreach\s*\(\s*\$(\w+)\s+as\s+\$(\w+)\s*\)\s*\{.*?\$\2->(\w+)[^;]*;/s', $content, $matches)) {
        foreach ($matches[3] as $index => $relationship) {
            $variable = $matches[2][$index];
            $collection = $matches[1][$index];

            // Skip common non-relationship calls (columns/attributes)
            $ignoredProperties = [
                'id',
                'name',
                'title',
                'description',
                'status',
                'type',
                'role',
                'email',
                'password',
                'remember_token',
                'created_at',
                'updated_at',
                'deleted_at',
                'email_verified_at',
                'image',
                'image_path',
                'avatar',
                'price',
                'amount',
                'quantity',
                'total',
                'subtotal',
                'tax',
                'discount',
                'slug',
                'content',
                'body',
                'subject',
                'message',
                'comment',
                'notes',
                'address',
                'city',
                'state',
                'country',
                'zip',
                'postal_code',
                'phone',
                'mobile',
                'fax',
                'website',
                'url',
                'link',
                'first_name',
                'last_name',
                'full_name',
                'username',
                'user_id',
                'agent_id',
                'customer_id',
                'property_id',
                'lead_id',
                'format',
                'count',
                'toArray',
                'diffForHumans',
                'sum',
                'avg',
                'min',
                'max'
            ];

            if (in_array($relationship, $ignoredProperties)) continue;

            // Check if eager loaded
            if (
                !preg_match('/with\s*\(\s*[\'"]' . preg_quote($relationship) . '[\'"]\s*\)/', $content) &&
                !preg_match('/\$this->' . preg_quote($relationship) . '/', $content)
            ) { // Skip if it's a method call on self

                $results['metrics']['issues_found']++;
                $results['metrics']['performance_issues']++;
                $results['issues'][] = [
                    'file' => $relativePath,
                    'type' => 'N+1 Query',
                    'severity' => 'MEDIUM',
                    'message' => "Potential N+1 query: accessing \${$variable}->{$relationship} inside loop without eager loading"
                ];
            }
        }
    }
}

// Run scan
foreach ($scanDirs as $dir) {
    scanDirectory($projectRoot . '/' . $dir, $results, $projectRoot, $excludeDirs);
}

// Output Report
echo "\n📊 Final Health Scan Report\n";
echo "==========================\n";
echo "Files Scanned: {$results['metrics']['files_scanned']}\n";
echo "Total Issues: {$results['metrics']['issues_found']}\n";

// Save report to JSON
file_put_contents('final_health_report.json', json_encode($results, JSON_PRETTY_PRINT));
echo "Report saved to final_health_report.json\n";
echo "  • Syntax Errors: {$results['metrics']['syntax_errors']}\n";
echo "  • Security Risks: {$results['metrics']['security_issues']}\n";
echo "  • Performance Issues: {$results['metrics']['performance_issues']}\n";

if (!empty($results['issues'])) {
    echo "\n📋 Detailed Issues:\n";
    echo "-------------------\n";
    foreach ($results['issues'] as $issue) {
        $icon = match ($issue['severity']) {
            'CRITICAL' => '🔴',
            'HIGH' => '🟠',
            'MEDIUM' => '🟡',
            'LOW' => '🔵',
            default => '⚪'
        };
        echo "{$icon} [{$issue['severity']}] {$issue['type']} in {$issue['file']}\n";
        echo "   {$issue['message']}\n\n";
    }
} else {
    echo "\n✅ No major issues found! Project health is good.\n";
}

// Save JSON report
$reportFile = 'final_health_report.json';
file_put_contents($reportFile, json_encode($results, JSON_PRETTY_PRINT));
echo "\n💾 Full report saved to: {$reportFile}\n";
