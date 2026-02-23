<?php

/**
 * APS Dream Home - Final Comprehensive Project Health Assessment
 * Complete deep scan and status report for the entire project
 */

class ProjectHealthScanner
{
    private $projectRoot;
    private $results = [];
    private $issues = [];
    private $warnings = [];
    private $recommendations = [];

    public function __construct($projectRoot)
    {
        $this->projectRoot = rtrim($projectRoot, '/');
        $this->results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'scan_type' => 'comprehensive_final_assessment',
            'overall_score' => 0,
            'categories' => [],
            'total_files_scanned' => 0,
            'total_issues_found' => 0,
            'total_warnings' => 0,
            'critical_issues' => 0,
            'performance_score' => 0,
            'security_score' => 0,
            'maintainability_score' => 0
        ];
    }

    public function runFullScan()
    {
        echo "🔍 Starting Comprehensive Project Health Assessment\n";
        echo "==================================================\n\n";

        $this->scanProjectStructure();
        $this->scanSecurityVulnerabilities();
        $this->scanCodeQuality();
        $this->scanPerformanceIssues();
        $this->scanConfigurationFiles();
        $this->scanDatabaseSchema();
        $this->scanTestingCoverage();
        $this->scanMonitoringSetup();
        $this->scanDeploymentReadiness();

        $this->calculateScores();
        $this->generateReport();

        return $this->results;
    }

    private function scanProjectStructure()
    {
        echo "📁 Scanning Project Structure...\n";

        $structureScore = 100;
        $structureIssues = 0;

        // Check for required directories
        $requiredDirs = [
            'app', 'config', 'database', 'public', 'resources', 'routes',
            'storage', 'tests', 'bootstrap', 'vendor'
        ];

        foreach ($requiredDirs as $dir) {
            if (!is_dir($this->projectRoot . '/' . $dir)) {
                $this->issues[] = "Missing required directory: {$dir}";
                $structureScore -= 10;
                $structureIssues++;
            }
        }

        // Check for important files
        $importantFiles = [
            'composer.json', 'composer.lock', 'artisan', '.env.example',
            'phpunit.xml', 'README.md', '.gitignore'
        ];

        foreach ($importantFiles as $file) {
            if (!file_exists($this->projectRoot . '/' . $file)) {
                $this->warnings[] = "Missing recommended file: {$file}";
                $structureScore -= 5;
            }
        }

        // Check storage permissions
        $storageDirs = ['storage', 'storage/logs', 'storage/cache', 'bootstrap/cache'];
        foreach ($storageDirs as $dir) {
            $path = $this->projectRoot . '/' . $dir;
            if (is_dir($path) && !is_writable($path)) {
                $this->issues[] = "Directory not writable: {$dir}";
                $structureScore -= 15;
                $structureIssues++;
            }
        }

        $this->results['categories']['structure'] = [
            'score' => max(0, $structureScore),
            'issues' => $structureIssues,
            'warnings' => count($this->warnings)
        ];

        echo "✅ Project structure scan completed\n\n";
    }

    private function scanSecurityVulnerabilities()
    {
        echo "🔒 Scanning Security Vulnerabilities...\n";

        $securityScore = 100;
        $securityIssues = 0;

        // Scan PHP files for security issues
        $phpFiles = $this->getFilesByExtension('php');

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for dangerous functions
            $dangerousFunctions = ['eval', 'exec', 'system', 'shell_exec', 'passthru', 'popen'];
            foreach ($dangerousFunctions as $func) {
                if (strpos($content, $func . '(') !== false) {
                    $this->issues[] = "Dangerous function found in {$file}: {$func}()";
                    $securityScore -= 20;
                    $securityIssues++;
                }
            }

            // Check for SQL injection patterns
            if (preg_match('/\$[a-zA-Z_][a-zA-Z0-9_]*.*=.*\$_[A-Z]+.*;/', $content)) {
                if (!preg_match('/prepare|bindParam|bindValue/', $content)) {
                    $this->warnings[] = "Potential SQL injection in {$file}";
                    $securityScore -= 10;
                }
            }

            // Check for XSS vulnerabilities
            if (strpos($content, 'echo $_') !== false || strpos($content, 'print $_') !== false) {
                $this->warnings[] = "Potential XSS vulnerability in {$file}";
                $securityScore -= 5;
            }

            // Check for hardcoded secrets
            if (preg_match('/password.*=.*[\'"][^\'"]*[\'"]/', $content) ||
                preg_match('/api_key.*=.*[\'"][^\'"]*[\'"]/', $content) ||
                preg_match('/secret.*=.*[\'"][^\'"]*[\'"]/', $content)) {
                $this->issues[] = "Hardcoded secret found in {$file}";
                $securityScore -= 15;
                $securityIssues++;
            }
        }

        // Check .env file
        $envFile = $this->projectRoot . '/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);

            // Check for placeholder values
            if (strpos($envContent, 'PLACEHOLDER') !== false ||
                strpos($envContent, 'YOUR_') !== false ||
                strpos($envContent, 'CHANGE_ME') !== false) {
                $this->warnings[] = "Environment file contains placeholder values";
                $securityScore -= 10;
            }

            // Check for APP_KEY
            if (!preg_match('/APP_KEY=base64:[A-Za-z0-9+\/=]+/', $envContent)) {
                $this->issues[] = "Invalid or missing APP_KEY in .env";
                $securityScore -= 20;
                $securityIssues++;
            }
        } else {
            $this->issues[] = "Missing .env file";
            $securityScore -= 25;
            $securityIssues++;
        }

        $this->results['categories']['security'] = [
            'score' => max(0, $securityScore),
            'issues' => $securityIssues,
            'warnings' => count($this->warnings) - $this->results['categories']['structure']['warnings']
        ];

        echo "✅ Security scan completed\n\n";
    }

    private function scanCodeQuality()
    {
        echo "💻 Scanning Code Quality...\n";

        $qualityScore = 100;
        $qualityIssues = 0;

        $phpFiles = $this->getFilesByExtension('php');

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $lines = explode("\n", $content);

            // Check file size (large files indicate potential refactoring needed)
            if (count($lines) > 500) {
                $this->warnings[] = "Large file detected: {$file} (" . count($lines) . " lines)";
                $qualityScore -= 5;
            }

            // Check for TODO/FIXME comments
            if (preg_match('/(?:TODO|FIXME|HACK)/i', $content)) {
                $this->warnings[] = "TODO/FIXME comment found in {$file}";
                $qualityScore -= 2;
            }

            // Check for debug code
            $debugPatterns = ['var_dump', 'print_r', 'dd(', 'dump(', 'console.log'];
            foreach ($debugPatterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $this->warnings[] = "Debug code found in {$file}: {$pattern}";
                    $qualityScore -= 3;
                }
            }

            // Check for proper error handling
            if (!preg_match('/try\s*\{.*\}/s', $content) && strpos($content, '$_') !== false) {
                $this->warnings[] = "Missing error handling in {$file}";
                $qualityScore -= 5;
            }

            $this->results['total_files_scanned']++;
        }

        // Check for code duplication (simplified check)
        $this->checkCodeDuplication();

        $this->results['categories']['code_quality'] = [
            'score' => max(0, $qualityScore),
            'issues' => $qualityIssues,
            'warnings' => count($this->warnings) - array_sum(array_column($this->results['categories'], 'warnings'))
        ];

        echo "✅ Code quality scan completed\n\n";
    }

    private function scanPerformanceIssues()
    {
        echo "⚡ Scanning Performance Issues...\n";

        $performanceScore = 100;
        $performanceIssues = 0;

        $phpFiles = $this->getFilesByExtension('php');

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Check for N+1 query patterns
            if (preg_match('/foreach.*\{[^}]*->.*\}/s', $content)) {
                if (!preg_match('/with\(|load\(/', $content)) {
                    $this->warnings[] = "Potential N+1 query in {$file}";
                    $performanceScore -= 10;
                }
            }

            // Check for memory-intensive operations
            if (strpos($content, 'file_get_contents') !== false && strpos($content, 'large') !== false) {
                $this->warnings[] = "Potential memory-intensive operation in {$file}";
                $performanceScore -= 5;
            }

            // Check for unoptimized database queries
            if (preg_match('/SELECT \* FROM/i', $content)) {
                $this->warnings[] = "SELECT * query found in {$file} - consider specifying columns";
                $performanceScore -= 2;
            }
        }

        // Check for caching implementation
        $cacheUsage = $this->checkCacheUsage();
        if (!$cacheUsage) {
            $this->recommendations[] = "Implement caching for frequently accessed data";
            $performanceScore -= 10;
        }

        $this->results['categories']['performance'] = [
            'score' => max(0, $performanceScore),
            'issues' => $performanceIssues,
            'warnings' => count($this->warnings) - array_sum(array_column($this->results['categories'], 'warnings'))
        ];

        echo "✅ Performance scan completed\n\n";
    }

    private function scanConfigurationFiles()
    {
        echo "⚙️ Scanning Configuration Files...\n";

        $configScore = 100;
        $configIssues = 0;

        // Check composer.json
        $composerFile = $this->projectRoot . '/composer.json';
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            if (!$composer) {
                $this->issues[] = "Invalid composer.json file";
                $configScore -= 20;
                $configIssues++;
            } else {
                // Check for required dependencies
                $requiredDeps = ['php'];
                foreach ($requiredDeps as $dep) {
                    if (!isset($composer['require'][$dep])) {
                        $this->warnings[] = "Missing required dependency: {$dep}";
                        $configScore -= 10;
                    }
                }
            }
        }

        // Check database configuration
        $dbConfigFiles = [
            'config/database.php',
            'app/config/database.php'
        ];

        $dbConfigured = false;
        foreach ($dbConfigFiles as $configFile) {
            if (file_exists($this->projectRoot . '/' . $configFile)) {
                $dbConfigured = true;
                break;
            }
        }

        if (!$dbConfigured) {
            $this->issues[] = "No database configuration found";
            $configScore -= 25;
            $configIssues++;
        }

        $this->results['categories']['configuration'] = [
            'score' => max(0, $configScore),
            'issues' => $configIssues,
            'warnings' => count($this->warnings) - array_sum(array_column($this->results['categories'], 'warnings'))
        ];

        echo "✅ Configuration scan completed\n\n";
    }

    private function scanDatabaseSchema()
    {
        echo "🗄️ Scanning Database Schema...\n";

        $dbScore = 100;
        $dbIssues = 0;

        // Check for migration files
        $migrationDir = $this->projectRoot . '/database/migrations';
        if (!is_dir($migrationDir)) {
            $this->issues[] = "Missing migrations directory";
            $dbScore -= 20;
            $dbIssues++;
        } else {
            $migrations = glob($migrationDir . '/*.php');
            if (empty($migrations)) {
                $this->warnings[] = "No migration files found";
                $dbScore -= 10;
            }
        }

        // Check for seeder files
        $seederDir = $this->projectRoot . '/database/seeders';
        if (is_dir($seederDir)) {
            $seeders = glob($seederDir . '/*.php');
            if (empty($seeders)) {
                $this->warnings[] = "No database seeders found";
                $dbScore -= 5;
            }
        }

        $this->results['categories']['database'] = [
            'score' => max(0, $dbScore),
            'issues' => $dbIssues,
            'warnings' => count($this->warnings) - array_sum(array_column($this->results['categories'], 'warnings'))
        ];

        echo "✅ Database schema scan completed\n\n";
    }

    private function scanTestingCoverage()
    {
        echo "🧪 Scanning Testing Coverage...\n";

        $testingScore = 100;
        $testingIssues = 0;

        // Check for test directory
        $testDir = $this->projectRoot . '/tests';
        if (!is_dir($testDir)) {
            $this->issues[] = "Missing tests directory";
            $testingScore -= 30;
            $testingIssues++;
        } else {
            $testFiles = $this->getFilesByExtension('php', $testDir);
            if (empty($testFiles)) {
                $this->issues[] = "No test files found";
                $testingScore -= 25;
                $testingIssues++;
            } else {
                // Basic check for test file naming
                $hasUnitTests = false;
                $hasFeatureTests = false;

                foreach ($testFiles as $file) {
                    if (strpos($file, '/Unit/') !== false) $hasUnitTests = true;
                    if (strpos($file, '/Feature/') !== false) $hasFeatureTests = true;
                }

                if (!$hasUnitTests) {
                    $this->warnings[] = "No unit tests found";
                    $testingScore -= 10;
                }
                if (!$hasFeatureTests) {
                    $this->warnings[] = "No feature tests found";
                    $testingScore -= 10;
                }
            }
        }

        // Check for PHPUnit configuration
        if (!file_exists($this->projectRoot . '/phpunit.xml')) {
            $this->warnings[] = "Missing PHPUnit configuration";
            $testingScore -= 10;
        }

        $this->results['categories']['testing'] = [
            'score' => max(0, $testingScore),
            'issues' => $testingIssues,
            'warnings' => count($this->warnings) - array_sum(array_column($this->results['categories'], 'warnings'))
        ];

        echo "✅ Testing coverage scan completed\n\n";
    }

    private function scanMonitoringSetup()
    {
        echo "📊 Scanning Monitoring Setup...\n";

        $monitoringScore = 100;
        $monitoringIssues = 0;

        // Check for logging configuration
        $logFiles = glob($this->projectRoot . '/logs/*.log');
        if (empty($logFiles)) {
            $this->warnings[] = "No log files found - monitoring may not be active";
            $monitoringScore -= 10;
        }

        // Check for monitoring services
        $monitoringFiles = [
            'app/Services/Monitoring/LoggingService.php',
            'app/Services/Monitoring/MonitoringService.php'
        ];

        foreach ($monitoringFiles as $file) {
            if (!file_exists($this->projectRoot . '/' . $file)) {
                $this->warnings[] = "Missing monitoring service: {$file}";
                $monitoringScore -= 15;
            }
        }

        // Check for health check endpoint
        $healthFile = $this->projectRoot . '/public/health.php';
        if (!file_exists($healthFile)) {
            $this->warnings[] = "Missing health check endpoint";
            $monitoringScore -= 10;
        }

        $this->results['categories']['monitoring'] = [
            'score' => max(0, $monitoringScore),
            'issues' => $monitoringIssues,
            'warnings' => count($this->warnings) - array_sum(array_column($this->results['categories'], 'warnings'))
        ];

        echo "✅ Monitoring setup scan completed\n\n";
    }

    private function scanDeploymentReadiness()
    {
        echo "🚀 Scanning Deployment Readiness...\n";

        $deploymentScore = 100;
        $deploymentIssues = 0;

        // Check for deployment scripts
        $deploymentFiles = [
            'deploy.php',
            'setup_production.php',
            'config/deployment.php'
        ];

        foreach ($deploymentFiles as $file) {
            if (!file_exists($this->projectRoot . '/' . $file)) {
                $this->warnings[] = "Missing deployment file: {$file}";
                $deploymentScore -= 10;
            }
        }

        // Check for CI/CD configuration
        $ciFiles = [
            '.github/workflows/ci.yml',
            '.gitlab-ci.yml',
            'Jenkinsfile'
        ];

        $hasCi = false;
        foreach ($ciFiles as $file) {
            if (file_exists($this->projectRoot . '/' . $file)) {
                $hasCi = true;
                break;
            }
        }

        if (!$hasCi) {
            $this->warnings[] = "No CI/CD configuration found";
            $deploymentScore -= 15;
        }

        // Check for production environment file
        if (!file_exists($this->projectRoot . '/.env.production') &&
            !file_exists($this->projectRoot . '/.env')) {
            $this->issues[] = "No production environment configuration";
            $deploymentScore -= 20;
            $deploymentIssues++;
        }

        $this->results['categories']['deployment'] = [
            'score' => max(0, $deploymentScore),
            'issues' => $deploymentIssues,
            'warnings' => count($this->warnings) - array_sum(array_column($this->results['categories'], 'warnings'))
        ];

        echo "✅ Deployment readiness scan completed\n\n";
    }

    private function calculateScores()
    {
        // Calculate overall score as weighted average
        $weights = [
            'security' => 0.25,      // 25% - most important
            'structure' => 0.15,     // 15%
            'code_quality' => 0.15,  // 15%
            'performance' => 0.15,   // 15%
            'configuration' => 0.10, // 10%
            'database' => 0.10,      // 10%
            'testing' => 0.05,       // 5%
            'monitoring' => 0.03,    // 3%
            'deployment' => 0.02     // 2%
        ];

        $overallScore = 0;
        foreach ($weights as $category => $weight) {
            if (isset($this->results['categories'][$category])) {
                $overallScore += $this->results['categories'][$category]['score'] * $weight;
            }
        }

        $this->results['overall_score'] = round($overallScore, 1);

        // Calculate component scores
        $this->results['security_score'] = $this->results['categories']['security']['score'];
        $this->results['performance_score'] = $this->results['categories']['performance']['score'];
        $this->results['maintainability_score'] = round(
            ($this->results['categories']['code_quality']['score'] +
             $this->results['categories']['structure']['score'] +
             $this->results['categories']['configuration']['score']) / 3,
            1
        );

        $this->results['total_issues_found'] = count($this->issues);
        $this->results['total_warnings'] = count($this->warnings);
        $this->results['critical_issues'] = count(array_filter($this->issues, function($issue) {
            return strpos($issue, 'Missing') !== false ||
                   strpos($issue, 'dangerous') !== false ||
                   strpos($issue, 'hardcoded') !== false ||
                   strpos($issue, 'Invalid') !== false;
        }));
    }

    private function generateReport()
    {
        echo "📊 FINAL PROJECT HEALTH ASSESSMENT REPORT\n";
        echo "=========================================\n\n";

        echo "🎯 OVERALL PROJECT SCORE: {$this->results['overall_score']}/100\n\n";

        // Score interpretation
        if ($this->results['overall_score'] >= 90) {
            echo "🎉 EXCELLENT: Project is production-ready with best practices implemented\n";
        } elseif ($this->results['overall_score'] >= 80) {
            echo "✅ GOOD: Project is ready for production with minor improvements needed\n";
        } elseif ($this->results['overall_score'] >= 70) {
            echo "⚠️ FAIR: Project needs some improvements before production\n";
        } elseif ($this->results['overall_score'] >= 60) {
            echo "🚨 POOR: Project requires significant improvements\n";
        } else {
            echo "❌ CRITICAL: Project needs major rework before deployment\n";
        }

        echo "\n📈 CATEGORY SCORES:\n";
        echo "-------------------\n";
        foreach ($this->results['categories'] as $category => $data) {
            $status = $data['score'] >= 80 ? '✅' : ($data['score'] >= 60 ? '⚠️' : '❌');
            printf("%s %-15s: %3d/100 (%d issues, %d warnings)\n",
                $status, ucwords(str_replace('_', ' ', $category)),
                $data['score'], $data['issues'], $data['warnings']
            );
        }

        echo "\n📋 SUMMARY STATISTICS:\n";
        echo "----------------------\n";
        echo "• Files Scanned: {$this->results['total_files_scanned']}\n";
        echo "• Total Issues: {$this->results['total_issues_found']}\n";
        echo "• Total Warnings: {$this->results['total_warnings']}\n";
        echo "• Critical Issues: {$this->results['critical_issues']}\n";
        echo "• Security Score: {$this->results['security_score']}/100\n";
        echo "• Performance Score: {$this->results['performance_score']}/100\n";
        echo "• Maintainability Score: {$this->results['maintainability_score']}/100\n";

        if (!empty($this->issues)) {
            echo "\n🚨 CRITICAL ISSUES FOUND:\n";
            echo "-------------------------\n";
            foreach ($this->issues as $issue) {
                echo "• {$issue}\n";
            }
        }

        if (!empty($this->warnings)) {
            echo "\n⚠️ WARNINGS:\n";
            echo "------------\n";
            foreach ($this->warnings as $warning) {
                echo "• {$warning}\n";
            }
        }

        if (!empty($this->recommendations)) {
            echo "\n💡 RECOMMENDATIONS:\n";
            echo "-------------------\n";
            foreach ($this->recommendations as $rec) {
                echo "• {$rec}\n";
            }
        }

        echo "\n📅 Assessment completed at: {$this->results['timestamp']}\n";

        // Save results to file
        $reportFile = $this->projectRoot . '/project_health_report_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($reportFile, json_encode($this->results, JSON_PRETTY_PRINT));
        echo "\n💾 Detailed report saved to: {$reportFile}\n";
    }

    private function getFilesByExtension($extension, $directory = null)
    {
        $directory = $directory ?: $this->projectRoot;
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === $extension) {
                // Skip vendor, node_modules, and other irrelevant directories
                $path = $file->getPathname();
                if (!preg_match('/\/(vendor|node_modules|\.git)\//', $path)) {
                    $files[] = $path;
                }
            }
        }

        return $files;
    }

    private function checkCodeDuplication()
    {
        // Simplified duplication check - in a real scenario, use tools like PHPCPD
        $phpFiles = $this->getFilesByExtension('php');
        $fileContents = [];

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $hash = md5($content);
            if (isset($fileContents[$hash])) {
                $this->warnings[] = "Potential code duplication between {$file} and {$fileContents[$hash]}";
            } else {
                $fileContents[$hash] = $file;
            }
        }
    }

    private function checkCacheUsage()
    {
        $phpFiles = $this->getFilesByExtension('php');
        $cacheUsage = false;

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            if (preg_match('/Cache::|cache\(\)|Redis|Memcached/i', $content)) {
                $cacheUsage = true;
                break;
            }
        }

        return $cacheUsage;
    }
}

// Run the assessment
$scanner = new ProjectHealthScanner(__DIR__);
$results = $scanner->runFullScan();

// Save final summary
$summaryFile = __DIR__ . '/project_health_summary_' . date('Y-m-d_H-i-s') . '.txt';
$summary = "
APS DREAM HOME - PROJECT HEALTH SUMMARY
======================================

Overall Score: {$results['overall_score']}/100
Security Score: {$results['security_score']}/100
Performance Score: {$results['performance_score']}/100
Maintainability Score: {$results['maintainability_score']}/100

Total Files Scanned: {$results['total_files_scanned']}
Critical Issues: {$results['critical_issues']}
Total Issues: {$results['total_issues_found']}
Total Warnings: {$results['total_warnings']}

Assessment Date: {$results['timestamp']}
";

file_put_contents($summaryFile, $summary);
echo "\n📄 Summary saved to: {$summaryFile}\n";

?>
