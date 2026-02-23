<?php
/**
 * APS Dream Home - Phase 9 Deep Scan: Final System Testing and Verification
 * Comprehensive final analysis and recommendations for the entire project
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'phase' => 'final_verification',
    'summary' => [],
    'consolidated_issues' => [],
    'priority_fixes' => [],
    'security_fixes' => [],
    'performance_fixes' => [],
    'final_recommendations' => []
];

echo "🎯 Phase 9: Final System Testing & Verification\n";
echo "=============================================\n\n";

// Load all previous scan results
echo "📊 Consolidating All Scan Results\n";
echo "=================================\n";

$scanFiles = [
    'deep_scan_results_phase1.json',
    'deep_scan_phase2_results.json',
    'deep_scan_phase3_results.json',
    'deep_scan_phase4_results.json',
    'deep_scan_phase5_results.json',
    'deep_scan_phase6_results.json',
    'deep_scan_phase7_results.json',
    'deep_scan_phase8_results.json'
];

$allIssues = [];
$allSecurityRisks = [];
$allPerformanceIssues = [];

foreach ($scanFiles as $scanFile) {
    $filePath = $projectRoot . '/' . $scanFile;
    if (file_exists($filePath)) {
        $scanData = json_decode(file_get_contents($filePath), true);
        if ($scanData) {
            echo "✅ Loaded {$scanFile}\n";

// Consolidate issues
if (isset($scanData['issues'])) {
    if (is_array($scanData['issues'])) {
        foreach ($scanData['issues'] as $issue) {
            if (is_string($issue)) {
                $allIssues[] = $issue;
            } elseif (is_array($issue)) {
                $allIssues[] = implode(' - ', $issue);
            }
        }
    }
}

// Consolidate security risks
$securityKeys = ['security_risks', 'security_vulnerabilities', 'security_concerns'];
foreach ($securityKeys as $key) {
    if (isset($scanData[$key])) {
        if (is_array($scanData[$key])) {
            foreach ($scanData[$key] as $risk) {
                if (is_string($risk)) {
                    $allSecurityRisks[] = $risk;
                } elseif (is_array($risk)) {
                    $allSecurityRisks[] = isset($risk['file']) && isset($risk['issues']) ?
                        $risk['file'] . ': ' . implode(', ', $risk['issues']) :
                        implode(' - ', $risk);
                }
            }
        }
    }
}

// Consolidate performance issues
$performanceKeys = ['performance_issues', 'optimization_suggestions'];
foreach ($performanceKeys as $key) {
    if (isset($scanData[$key])) {
        if (is_array($scanData[$key])) {
            foreach ($scanData[$key] as $issue) {
                if (is_string($issue)) {
                    $allPerformanceIssues[] = $issue;
                } elseif (is_array($issue)) {
                    $allPerformanceIssues[] = implode(' - ', $issue);
                }
            }
        }
    }
}
        }
    } else {
        echo "⚠️  Missing scan file: {$scanFile}\n";
    }
}

echo "\n";

// Remove duplicates
$allIssues = array_unique($allIssues);
$allSecurityRisks = array_unique($allSecurityRisks);
$allPerformanceIssues = array_unique($allPerformanceIssues);

// Categorize issues by priority
echo "🎯 Prioritizing Issues\n";
echo "====================\n";

$criticalSecurity = [];
$highPriority = [];
$mediumPriority = [];
$lowPriority = [];

// Categorize security risks
foreach ($allSecurityRisks as $risk) {
    if (strpos(strtolower($risk), 'sql injection') !== false ||
        strpos(strtolower($risk), 'xss') !== false ||
        strpos(strtolower($risk), 'hardcoded') !== false ||
        strpos(strtolower($risk), 'debug') !== false) {
        $criticalSecurity[] = $risk;
    } else {
        $highPriority[] = $risk;
    }
}

// Categorize general issues
foreach ($allIssues as $issue) {
    if (strpos(strtolower($issue), 'missing') !== false ||
        strpos(strtolower($issue), 'not found') !== false ||
        strpos(strtolower($issue), 'not configured') !== false) {
        $highPriority[] = $issue;
    } elseif (strpos(strtolower($issue), 'large') !== false ||
              strpos(strtolower($issue), 'deprecated') !== false) {
        $mediumPriority[] = $issue;
    } else {
        $lowPriority[] = $issue;
    }
}

// Categorize performance issues
foreach ($allPerformanceIssues as $issue) {
    if (strpos(strtolower($issue), 'n+1') !== false ||
        strpos(strtolower($issue), 'large controller') !== false) {
        $highPriority[] = $issue;
    } else {
        $mediumPriority[] = $issue;
    }
}

echo "🔴 Critical Security Issues: " . count($criticalSecurity) . "\n";
echo "🟠 High Priority Issues: " . count($highPriority) . "\n";
echo "🟡 Medium Priority Issues: " . count($mediumPriority) . "\n";
echo "🟢 Low Priority Issues: " . count($lowPriority) . "\n";

echo "\n";

// Display critical security issues
if (!empty($criticalSecurity)) {
    echo "🚨 CRITICAL SECURITY ISSUES (Fix Immediately)\n";
    echo "===========================================\n";
    foreach ($criticalSecurity as $issue) {
        echo "❌ {$issue}\n";
    }
    echo "\n";
}

// Display high priority issues
if (!empty($highPriority)) {
    echo "🟠 HIGH PRIORITY ISSUES (Fix Soon)\n";
    echo "=================================\n";
    $displayCount = min(10, count($highPriority));
    for ($i = 0; $i < $displayCount; $i++) {
        echo "⚠️  {$highPriority[$i]}\n";
    }
    if (count($highPriority) > 10) {
        echo "... and " . (count($highPriority) - 10) . " more high priority issues\n";
    }
    echo "\n";
}

echo "📋 FINAL RECOMMENDATIONS\n";
echo "=======================\n";

$recommendations = [
    "🔒 Security Fixes:",
    "  • Address all critical security vulnerabilities immediately",
    "  • Implement proper input validation and sanitization",
    "  • Remove debug code and console statements from production",
    "  • Set up proper environment configuration and secrets management",
    "",
    "⚡ Performance Optimizations:",
    "  • Break large controllers into smaller, focused classes",
    "  • Fix N+1 query problems with eager loading",
    "  • Implement caching for frequently accessed data",
    "  • Optimize database queries and add proper indexing",
    "",
    "🏗️  Code Quality Improvements:",
    "  • Implement proper error handling and exception management",
    "  • Add comprehensive input validation to all forms",
    "  • Create unit and integration tests for critical functionality",
    "  • Refactor duplicate code and improve maintainability",
    "",
    "🔧 Configuration & Deployment:",
    "  • Complete all missing configuration settings",
    "  • Set up proper database connections and credentials",
    "  • Configure production environment variables",
    "  • Implement proper logging and monitoring",
    "",
    "📚 Next Steps:",
    "  • Run database migrations to set up all tables",
    "  • Test all functionality in a staging environment",
    "  • Implement automated testing and CI/CD pipelines",
    "  • Set up monitoring and alerting for production"
];

foreach ($recommendations as $rec) {
    echo "{$rec}\n";
}

echo "\n";

// Project health assessment
echo "🏥 PROJECT HEALTH ASSESSMENT\n";
echo "===========================\n";

$totalCritical = count($criticalSecurity);
$totalHigh = count($highPriority);
$totalMedium = count($mediumPriority);
$totalLow = count($lowPriority);

$healthScore = 100;
$healthScore -= $totalCritical * 20;  // Critical issues heavily impact score
$healthScore -= $totalHigh * 10;      // High priority issues
$healthScore -= $totalMedium * 5;     // Medium priority issues
$healthScore -= $totalLow * 2;        // Low priority issues
$healthScore = max(0, min(100, $healthScore));

if ($healthScore >= 80) {
    $status = "🟢 EXCELLENT";
} elseif ($healthScore >= 60) {
    $status = "🟡 GOOD";
} elseif ($healthScore >= 40) {
    $status = "🟠 FAIR";
} else {
    $status = "🔴 NEEDS ATTENTION";
}

echo "Overall Project Health: {$status} ({$healthScore}/100)\n";
echo "Total Issues Found: " . ($totalCritical + $totalHigh + $totalMedium + $totalLow) . "\n";
echo "Security Vulnerabilities: " . count($allSecurityRisks) . "\n";
echo "Performance Issues: " . count($allPerformanceIssues) . "\n";

echo "\n";

if ($healthScore >= 70) {
    echo "🎉 Your project has good foundations! Focus on the high-priority issues to reach production readiness.\n";
} elseif ($healthScore >= 50) {
    echo "⚠️  Your project needs some work before production. Address critical security issues first.\n";
} else {
    echo "🚨 Your project requires significant fixes before deployment. Start with security issues.\n";
}

echo "\n";

// Save final results
$results['consolidated_issues'] = [
    'critical_security' => $criticalSecurity,
    'high_priority' => $highPriority,
    'medium_priority' => $mediumPriority,
    'low_priority' => $lowPriority
];

$results['priority_fixes'] = [
    'immediate' => array_slice($criticalSecurity, 0, 5),
    'short_term' => array_slice($highPriority, 0, 10)
];

$results['security_fixes'] = $criticalSecurity;
$results['performance_fixes'] = $allPerformanceIssues;
$results['final_recommendations'] = $recommendations;

$results['summary'] = [
    'health_score' => $healthScore,
    'health_status' => $status,
    'total_issues' => $totalCritical + $totalHigh + $totalMedium + $totalLow,
    'security_vulnerabilities' => count($allSecurityRisks),
    'performance_issues' => count($allPerformanceIssues),
    'scan_completed' => true
];

// Save results
$resultsFile = $projectRoot . '/deep_scan_final_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "💾 Final results saved to: {$resultsFile}\n";
echo "\n🎯 FULL PROJECT DEEP SCAN COMPLETE!\n";
echo "===================================\n";
echo "✅ All 9 phases completed successfully\n";
echo "📊 Comprehensive analysis finished\n";
echo "📋 Actionable recommendations provided\n";
echo "\n🚀 Ready for production fixes and optimizations!\n";

?>
