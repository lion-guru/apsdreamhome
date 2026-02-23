<?php
/**
 * APS Dream Home - Final Project Health Assessment
 * Consolidate all scan results and provide final health score
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m_d_H_i_s'),
    'phase' => 'final_health_assessment',
    'scan_results' => [],
    'overall_health_score' => 0,
    'health_grade' => '',
    'critical_issues_remaining' => 0,
    'recommendations' => [],
    'next_steps' => []
];

echo "🏥 FINAL PROJECT HEALTH ASSESSMENT\n";
echo "==================================\n\n";

// Function to load and consolidate scan results
function loadScanResults($projectRoot, &$results) {
    $scanFiles = [
        'full_project_deep_scan.json',
        'phase2_php_scan.json',
        'phase3_blade_scan.json',
        'phase4_js_css_scan.json',
        'phase5_database_scan.json',
        'phase6_config_scan.json',
        'phase7_dependencies_scan.json',
        'phase8_routing_scan.json',
        'phase9_final_verification.json',
        'xss_fixes_results.json',
        'debug_removal_results.json',
        'controller_refactoring_analysis.json',
        'n_plus_one_query_fixes.json',
        'database_index_optimization.json',
        'config_environment_completion.json'
    ];

    $consolidatedData = [
        'security_issues' => 0,
        'performance_issues' => 0,
        'configuration_issues' => 0,
        'code_quality_issues' => 0,
        'dependency_issues' => 0,
        'fixes_applied' => 0,
        'backups_created' => 0
    ];

    foreach ($scanFiles as $scanFile) {
        $filePath = $projectRoot . '/' . $scanFile;
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            if ($data) {
                $results['scan_results'][] = [
                    'phase' => str_replace(['.json', '_'], ['', ' '], $scanFile),
                    'data' => $data
                ];

                // Extract key metrics
                if (isset($data['summary'])) {
                    if (isset($data['summary']['security_vulnerabilities_found'])) {
                        $consolidatedData['security_issues'] += $data['summary']['security_vulnerabilities_found'];
                    }
                    if (isset($data['summary']['performance_issues_found'])) {
                        $consolidatedData['performance_issues'] += $data['summary']['performance_issues_found'];
                    }
                    if (isset($data['summary']['config_issues_found'])) {
                        $consolidatedData['configuration_issues'] += $data['summary']['config_issues_found'];
                    }
                    if (isset($data['summary']['code_quality_issues'])) {
                        $consolidatedData['code_quality_issues'] += $data['summary']['code_quality_issues'];
                    }
                    if (isset($data['summary']['dependency_conflicts'])) {
                        $consolidatedData['dependency_issues'] += $data['summary']['dependency_conflicts'];
                    }
                    if (isset($data['summary']['fixes_applied'])) {
                        $consolidatedData['fixes_applied'] += $data['summary']['fixes_applied'];
                    }
                    if (isset($data['summary']['backups_created'])) {
                        $consolidatedData['backups_created'] += $data['summary']['backups_created'];
                    }
                }

                // Handle specific scan types
                if (strpos($scanFile, 'xss_fixes') !== false && isset($data['summary']['xss_vulnerabilities_fixed'])) {
                    $consolidatedData['fixes_applied'] += $data['summary']['xss_vulnerabilities_fixed'];
                }
                if (strpos($scanFile, 'debug_removal') !== false && isset($data['summary']['debug_statements_removed'])) {
                    $consolidatedData['fixes_applied'] += $data['summary']['debug_statements_removed'];
                }
                if (strpos($scanFile, 'n_plus_one') !== false && isset($data['summary']['auto_fixes_applied'])) {
                    $consolidatedData['fixes_applied'] += $data['summary']['auto_fixes_applied'];
                }
            }
        }
    }

    return $consolidatedData;
}

// Function to calculate health score
function calculateHealthScore($consolidatedData, &$results) {
    $totalIssues = $consolidatedData['security_issues'] +
                   $consolidatedData['performance_issues'] +
                   $consolidatedData['configuration_issues'] +
                   $consolidatedData['code_quality_issues'] +
                   $consolidatedData['dependency_issues'];

    $fixesApplied = $consolidatedData['fixes_applied'];

    // Base score starts at 100, deduct points for issues
    $healthScore = 100;

    // Deduct for security issues (most critical)
    $healthScore -= min($consolidatedData['security_issues'] * 10, 40);

    // Deduct for performance issues
    $healthScore -= min($consolidatedData['performance_issues'] * 5, 25);

    // Deduct for configuration issues
    $healthScore -= min($consolidatedData['configuration_issues'] * 3, 15);

    // Deduct for code quality issues
    $healthScore -= min($consolidatedData['code_quality_issues'] * 2, 10);

    // Deduct for dependency issues
    $healthScore -= min($consolidatedData['dependency_issues'] * 2, 10);

    // Ensure score doesn't go below 0
    $healthScore = max($healthScore, 0);

    $results['overall_health_score'] = round($healthScore);

    // Determine grade
    if ($healthScore >= 90) {
        $results['health_grade'] = 'A - Excellent';
    } elseif ($healthScore >= 80) {
        $results['health_grade'] = 'B - Good';
    } elseif ($healthScore >= 70) {
        $results['health_grade'] = 'C - Fair';
    } elseif ($healthScore >= 60) {
        $results['health_grade'] = 'D - Needs Improvement';
    } else {
        $results['health_grade'] = 'F - Critical Issues';
    }

    return [
        'total_issues' => $totalIssues,
        'fixes_applied' => $fixesApplied,
        'health_score' => $healthScore
    ];
}

// Load and consolidate results
echo "🔍 Loading Scan Results\n";
echo "=======================\n";

$consolidatedData = loadScanResults($projectRoot, $results);
echo "📊 Loaded " . count($results['scan_results']) . " scan result files\n\n";

// Calculate health score
echo "🧮 Calculating Health Score\n";
echo "===========================\n";

$healthMetrics = calculateHealthScore($consolidatedData, $results);
echo "💯 Overall Health Score: {$results['overall_health_score']}/100 ({$results['health_grade']})\n\n";

// Generate recommendations and next steps
echo "📋 Assessment Summary\n";
echo "=====================\n";

echo "🔒 Security Issues: {$consolidatedData['security_issues']}\n";
echo "⚡ Performance Issues: {$consolidatedData['performance_issues']}\n";
echo "⚙️ Configuration Issues: {$consolidatedData['configuration_issues']}\n";
echo "💻 Code Quality Issues: {$consolidatedData['code_quality_issues']}\n";
echo "📦 Dependency Issues: {$consolidatedData['dependency_issues']}\n";
echo "🔧 Fixes Applied: {$consolidatedData['fixes_applied']}\n";
echo "💾 Backups Created: {$consolidatedData['backups_created']}\n\n";

// Recommendations based on health score
if ($results['overall_health_score'] >= 80) {
    $results['recommendations'][] = "Project is in good health! Continue with regular maintenance.";
    $results['next_steps'][] = "Set up automated testing and continuous integration";
    $results['next_steps'][] = "Implement monitoring and logging";
    $results['next_steps'][] = "Plan for production deployment";
} elseif ($results['overall_health_score'] >= 60) {
    $results['recommendations'][] = "Address remaining critical and high-priority issues";
    $results['recommendations'][] = "Implement comprehensive testing";
    $results['recommendations'][] = "Review and optimize database queries";
    $results['next_steps'][] = "Fix remaining security vulnerabilities";
    $results['next_steps'][] = "Refactor large controllers";
    $results['next_steps'][] = "Add comprehensive error handling";
} else {
    $results['recommendations'][] = "URGENT: Address critical security vulnerabilities immediately";
    $results['recommendations'][] = "Fix configuration issues before deployment";
    $results['recommendations'][] = "Implement proper error handling and logging";
    $results['next_steps'][] = "Complete security fixes (SQL injection, XSS, etc.)";
    $results['next_steps'][] = "Fix performance issues (N+1 queries, large controllers)";
    $results['next_steps'][] = "Set up proper development environment";
    $results['next_steps'][] = "Implement automated testing";
}

echo "🏆 Recommendations\n";
echo "=================\n";
foreach ($results['recommendations'] as $rec) {
    echo "• {$rec}\n";
}

echo "\n🚀 Next Steps\n";
echo "=============\n";
foreach ($results['next_steps'] as $step) {
    echo "• {$step}\n";
}

echo "\n✅ Final Assessment Complete!\n";

$results['summary'] = [
    'total_scan_files' => count($results['scan_results']),
    'total_issues_found' => $healthMetrics['total_issues'],
    'total_fixes_applied' => $healthMetrics['fixes_applied'],
    'overall_health_score' => $results['overall_health_score'],
    'health_grade' => $results['health_grade']
];

// Save final assessment
$resultsFile = $projectRoot . '/final_health_assessment.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n🎯 Project Deep Scan and Fixes - COMPLETE!\n";

?>
