<?php

/**
 * Maximum Level Deep Project Scanner for APS Dream Home
 * Comprehensive analysis of entire project structure, code quality, and functionality
 */

class DeepProjectScanner
{
    private $projectPath;
    private $issues = [];
    private $stats = [];
    private $findings = [];

    public function __construct($projectPath)
    {
        $this->projectPath = $projectPath;
    }

    /**
     * Run complete deep scan
     */
    public function runDeepScan()
    {
        echo "=== MAXIMUM LEVEL DEEP PROJECT SCANNER ===\n";
        echo "Project: APS Dream Home\n";
        echo "Scan Time: " . date('Y-m-d H:i:s') . "\n\n";

        $this->scanProjectStructure();
        $this->scanPHPFiles();
        $this->scanDatabaseSchema();
        $this->scanRoutesAndControllers();
        $this->scanConfigurationFiles();
        $this->scanSecurityIssues();
        $this->scanOpenCodeFunctionality();
        $this->scanDependencies();
        $this->scanEnvironmentSetup();
        $this->generateComprehensiveReport();

        return $this->findings;
    }

    /**
     * Scan project structure
     */
    private function scanProjectStructure()
    {
        echo "[1/10] Scanning project structure...\n";

        $this->stats['file_types'] = []; // Initialize array
        $this->scanDirectory($this->projectPath);

        $this->stats['total_directories'] = $this->countDirectories($this->projectPath);
        $this->stats['total_files'] = $this->countFiles($this->projectPath);
        $this->stats['php_files'] = count(glob($this->projectPath . '/**/*.php', GLOB_BRACE));
        $this->stats['js_files'] = count(glob($this->projectPath . '/**/*.js', GLOB_BRACE));
        $this->stats['css_files'] = count(glob($this->projectPath . '/**/*.css', GLOB_BRACE));

        echo "  Directories: " . $this->stats['total_directories'] . "\n";
        echo "  Total Files: " . $this->stats['total_files'] . "\n";
        echo "  PHP Files: " . $this->stats['php_files'] . "\n";
        echo "  JS Files: " . $this->stats['js_files'] . "\n";
        echo "  CSS Files: " . $this->stats['css_files'] . "\n\n";
    }

    /**
     * Scan all PHP files for errors
     */
    private function scanPHPFiles()
    {
        echo "[2/10] Scanning PHP files for errors...\n";

        $phpFiles = glob($this->projectPath . '/**/*.php', GLOB_BRACE);
        $syntaxErrors = [];
        $runtimeErrors = [];
        $deprecatedUsage = [];
        $securityIssues = [];

        foreach ($phpFiles as $file) {
            $relativePath = str_replace($this->projectPath . '/', '', $file);

            // Check for syntax errors
            $output = shell_exec("php -l " . escapeshellarg($file) . " 2>&1");
            if (strpos($output, 'Parse error') !== false || strpos($output, 'Fatal error') !== false) {
                $syntaxErrors[] = [
                    'file' => $relativePath,
                    'error' => trim($output),
                    'severity' => 'critical'
                ];
            }

            // Check file content for common issues
            $content = file_get_contents($file);

            // Check for deprecated functions
            $deprecatedFunctions = ['mysql_', 'ereg', 'split(', 'each('];
            foreach ($deprecatedFunctions as $func) {
                if (strpos($content, $func) !== false) {
                    $deprecatedUsage[] = [
                        'file' => $relativePath,
                        'issue' => "Deprecated function usage: $func",
                        'severity' => 'medium'
                    ];
                }
            }

            // Check for security issues
            if (strpos($content, '$_GET') !== false || strpos($content, '$_POST') !== false) {
                if (
                    strpos($content, 'mysqli_real_escape_string') === false &&
                    strpos($content, 'htmlspecialchars') === false
                ) {
                    $securityIssues[] = [
                        'file' => $relativePath,
                        'issue' => 'Direct use of $_GET/$_POST without sanitization',
                        'severity' => 'high'
                    ];
                }
            }

            // Check for hardcoded credentials
            if (preg_match('/password\s*=\s*[\'"].*[\'"]/', $content)) {
                $securityIssues[] = [
                    'file' => $relativePath,
                    'issue' => 'Potential hardcoded password detected',
                    'severity' => 'critical'
                ];
            }

            // Check for SQL injection risks
            if (preg_match('/["\'].*\$.*["\'].*SELECT/i', $content)) {
                $securityIssues[] = [
                    'file' => $relativePath,
                    'issue' => 'Potential SQL injection vulnerability',
                    'severity' => 'critical'
                ];
            }
        }

        $this->findings['php_syntax_errors'] = $syntaxErrors;
        $this->findings['php_deprecated_usage'] = $deprecatedUsage;
        $this->findings['php_security_issues'] = $securityIssues;

        echo "  Syntax Errors: " . count($syntaxErrors) . "\n";
        echo "  Deprecated Usage: " . count($deprecatedUsage) . "\n";
        echo "  Security Issues: " . count($securityIssues) . "\n\n";
    }

    /**
     * Scan database schema
     */
    private function scanDatabaseSchema()
    {
        echo "[3/10] Scanning database schema...\n";

        try {
            $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $this->stats['database_tables'] = count($tables);

            $schemaIssues = [];
            $missingIndexes = [];
            $orphanedTables = [];

            foreach ($tables as $table) {
                // Check for tables without indexes
                $indexes = $pdo->query("SHOW INDEX FROM $table")->fetchAll(PDO::FETCH_ASSOC);
                if (count($indexes) <= 1) { // Only PRIMARY
                    $missingIndexes[] = [
                        'table' => $table,
                        'issue' => 'Table has no secondary indexes',
                        'severity' => 'medium'
                    ];
                }

                // Check for orphaned tables (no relationships)
                $columns = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_COLUMN);
                $hasForeignKeys = false;
                foreach ($columns as $column) {
                    if (strpos($column, '_id') !== false) {
                        $hasForeignKeys = true;
                        break;
                    }
                }

                if (!$hasForeignKeys && $table !== 'migrations' && strpos($table, '_') === false) {
                    $orphanedTables[] = [
                        'table' => $table,
                        'issue' => 'Potential orphaned table (no relationships detected)',
                        'severity' => 'low'
                    ];
                }
            }

            // Check for missing users table (critical)
            if (!in_array('users', $tables)) {
                $schemaIssues[] = [
                    'table' => 'users',
                    'issue' => 'Critical users table missing',
                    'severity' => 'critical'
                ];
            }

            $this->findings['database_schema_issues'] = $schemaIssues;
            $this->findings['database_missing_indexes'] = $missingIndexes;
            $this->findings['database_orphaned_tables'] = $orphanedTables;

            echo "  Total Tables: " . count($tables) . "\n";
            echo "  Schema Issues: " . count($schemaIssues) . "\n";
            echo "  Missing Indexes: " . count($missingIndexes) . "\n";
            echo "  Orphaned Tables: " . count($orphanedTables) . "\n\n";
        } catch (PDOException $e) {
            $this->findings['database_connection_error'] = [
                'error' => $e->getMessage(),
                'severity' => 'critical'
            ];
            echo "  ❌ Database connection failed: " . $e->getMessage() . "\n\n";
        }
    }

    /**
     * Scan routes and controllers
     */
    private function scanRoutesAndControllers()
    {
        echo "[4/10] Scanning routes and controllers...\n";

        $routesFile = $this->projectPath . '/routes/web.php';
        $apiRoutesFile = $this->projectPath . '/routes/api.php';

        $routeIssues = [];
        $controllerIssues = [];
        $orphanedRoutes = [];

        // Scan web routes
        if (file_exists($routesFile)) {
            $routesContent = file_get_contents($routesFile);

            // Check for duplicate routes
            preg_match_all('/Router::(get|post|put|delete|patch)\s*\(\s*[\'"](.*?)[\'"]\s*,/s', $routesContent, $matches);
            $routePaths = $matches[2];
            $duplicates = array_diff_assoc($routePaths, array_unique($routePaths));

            if (!empty($duplicates)) {
                $routeIssues[] = [
                    'file' => 'routes/web.php',
                    'issue' => 'Duplicate routes detected',
                    'duplicates' => array_values($duplicates),
                    'severity' => 'high'
                ];
            }

            $this->stats['web_routes'] = count($routePaths);
        }

        // Scan API routes
        if (file_exists($apiRoutesFile)) {
            $apiRoutesContent = file_get_contents($apiRoutesFile);
            preg_match_all('/\$router->(get|post|put|delete|patch)\s*\(\s*[\'"](.*?)[\'"]\s*,/s', $apiRoutesContent, $apiMatches);
            $this->stats['api_routes'] = count($apiMatches[2]);
        }

        // Scan controllers
        $controllers = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        foreach ($controllers as $controller) {
            $relativePath = str_replace($this->projectPath . '/', '', $controller);
            $content = file_get_contents($controller);

            // Check for undefined parent class
            if (preg_match('/class\s+\w+\s+extends\s+(\w+)/', $content, $matches)) {
                $parentClass = $matches[1];
                if (
                    $parentClass !== 'BaseController' &&
                    !in_array($parentClass, ['Controller', 'Model'])
                ) {
                    $controllerIssues[] = [
                        'file' => $relativePath,
                        'issue' => "Unconventional parent class: $parentClass",
                        'severity' => 'low'
                    ];
                }
            }

            // Check for missing methods referenced in routes
            preg_match_all('/public\s+function\s+(\w+)/', $content, $methods);
            // This would require cross-referencing with routes - simplified check
        }

        $this->findings['route_issues'] = $routeIssues;
        $this->findings['controller_issues'] = $controllerIssues;

        echo "  Web Routes: " . ($this->stats['web_routes'] ?? 0) . "\n";
        echo "  API Routes: " . ($this->stats['api_routes'] ?? 0) . "\n";
        echo "  Controllers: " . count($controllers) . "\n";
        echo "  Route Issues: " . count($routeIssues) . "\n";
        echo "  Controller Issues: " . count($controllerIssues) . "\n\n";
    }

    /**
     * Scan configuration files
     */
    private function scanConfigurationFiles()
    {
        echo "[5/10] Scanning configuration files...\n";

        $configFiles = [
            '.env',
            '.env.example',
            'config/database.php',
            'config/app.php',
            'composer.json',
            'package.json'
        ];

        $configIssues = [];

        foreach ($configFiles as $configFile) {
            $fullPath = $this->projectPath . '/' . $configFile;

            if (!file_exists($fullPath)) {
                $configIssues[] = [
                    'file' => $configFile,
                    'issue' => 'Configuration file missing',
                    'severity' => $configFile === '.env' ? 'critical' : 'medium'
                ];
            } else {
                $content = file_get_contents($fullPath);

                // Check for hardcoded credentials in config
                if (preg_match('/password\s*=\s*[\'"].*[\'"]/', $content) && strpos($content, '.env') === false) {
                    $configIssues[] = [
                        'file' => $configFile,
                        'issue' => 'Potential hardcoded credentials in config',
                        'severity' => 'critical'
                    ];
                }

                // Check for debugging enabled in production
                if (strpos($content, 'debug') !== false && strpos($content, 'true') !== false) {
                    $configIssues[] = [
                        'file' => $configFile,
                        'issue' => 'Debug mode may be enabled',
                        'severity' => 'medium'
                    ];
                }
            }
        }

        $this->findings['configuration_issues'] = $configIssues;

        echo "  Config Files Checked: " . count($configFiles) . "\n";
        echo "  Issues Found: " . count($configIssues) . "\n\n";
    }

    /**
     * Scan security issues
     */
    private function scanSecurityIssues()
    {
        echo "[6/10] Scanning security vulnerabilities...\n";

        $securityIssues = [];

        // Check for .git exposure
        if (is_dir($this->projectPath . '/.git')) {
            $gitConfig = file_get_contents($this->projectPath . '/.git/config');
            if (strpos($gitConfig, 'github.com') !== false) {
                $securityIssues[] = [
                    'issue' => 'Git repository exposed',
                    'severity' => 'medium',
                    'recommendation' => 'Ensure .git is in .gitignore and not deployed'
                ];
            }
        }

        // Check for sensitive files exposed
        $sensitiveFiles = ['.env', 'config/database.php', 'composer.json'];
        foreach ($sensitiveFiles as $file) {
            if (file_exists($this->projectPath . '/' . $file)) {
                $securityIssues[] = [
                    'file' => $file,
                    'issue' => 'Sensitive file may be accessible',
                    'severity' => 'high',
                    'recommendation' => 'Ensure proper .htaccess or web server configuration'
                ];
            }
        }

        // Check for XSS vulnerabilities
        $viewFiles = glob($this->projectPath . '/app/views/**/*.php', GLOB_BRACE);
        $xssIssues = [];
        foreach ($viewFiles as $viewFile) {
            $content = file_get_contents($viewFile);
            $relativePath = str_replace($this->projectPath . '/', '', $viewFile);

            // Check for unescaped output
            if (preg_match('/<\?=\s*\$[a-z_]+\s*\?>/i', $content)) {
                $xssIssues[] = [
                    'file' => $relativePath,
                    'issue' => 'Potential XSS vulnerability - unescaped variable output',
                    'severity' => 'high'
                ];
            }
        }

        // Check for CSRF protection
        $controllerFiles = glob($this->projectPath . '/app/Http/Controllers/**/*.php', GLOB_BRACE);
        $csrfIssues = [];
        foreach ($controllerFiles as $controller) {
            $content = file_get_contents($controller);
            $relativePath = str_replace($this->projectPath . '/', '', $controller);

            // Check for POST methods without CSRF protection
            if (strpos($content, 'public function') !== false && strpos($content, 'POST') !== false) {
                if (strpos($content, 'csrf') === false && strpos($content, 'CSRF') === false) {
                    $csrfIssues[] = [
                        'file' => $relativePath,
                        'issue' => 'POST method without CSRF protection',
                        'severity' => 'high'
                    ];
                }
            }
        }

        $this->findings['security_issues'] = array_merge($securityIssues, $xssIssues, $csrfIssues);

        echo "  Security Issues: " . count($securityIssues) . "\n";
        echo "  XSS Vulnerabilities: " . count($xssIssues) . "\n";
        echo "  CSRF Issues: " . count($csrfIssues) . "\n\n";
    }

    /**
     * Scan OpenCode functionality
     */
    private function scanOpenCodeFunctionality()
    {
        echo "[7/10] Scanning OpenCode functionality...\n";

        $openCodeFiles = [];
        $openCodeIssues = [];

        // Look for OpenCode related files
        $possibleOpenCodeFiles = [
            'opencode.php',
            'open-code.php',
            'app/Services/OpenCodeService.php',
            'app/Http/Controllers/OpenCodeController.php',
            'routes/opencode.php'
        ];

        foreach ($possibleOpenCodeFiles as $file) {
            $fullPath = $this->projectPath . '/' . $file;
            if (file_exists($fullPath)) {
                $openCodeFiles[] = $file;
                $content = file_get_contents($fullPath);

                // Check for errors
                $output = shell_exec("php -l " . escapeshellarg($fullPath) . " 2>&1");
                if (strpos($output, 'Parse error') !== false) {
                    $openCodeIssues[] = [
                        'file' => $file,
                        'error' => trim($output),
                        'severity' => 'critical'
                    ];
                }
            }
        }

        // Search for OpenCode references in code
        $allPHPFiles = glob($this->projectPath . '/**/*.php', GLOB_BRACE);
        $openCodeReferences = [];
        foreach ($allPHPFiles as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace($this->projectPath . '/', '', $file);

            if (stripos($content, 'opencode') !== false || stripos($content, 'open_code') !== false) {
                $openCodeReferences[] = $relativePath;
            }
        }

        $this->findings['opencode_files'] = $openCodeFiles;
        $this->findings['opencode_issues'] = $openCodeIssues;
        $this->findings['opencode_references'] = $openCodeReferences;

        echo "  OpenCode Files Found: " . count($openCodeFiles) . "\n";
        echo "  OpenCode Issues: " . count($openCodeIssues) . "\n";
        echo "  Code References: " . count($openCodeReferences) . "\n\n";
    }

    /**
     * Scan dependencies
     */
    private function scanDependencies()
    {
        echo "[8/10] Scanning dependencies...\n";

        $dependencyIssues = [];

        // Check composer.json
        $composerJson = $this->projectPath . '/composer.json';
        if (file_exists($composerJson)) {
            $composer = json_decode(file_get_contents($composerJson), true);
            if ($composer === null) {
                $dependencyIssues[] = [
                    'file' => 'composer.json',
                    'issue' => 'Invalid JSON format',
                    'severity' => 'critical'
                ];
            } else {
                $this->stats['php_dependencies'] = count($composer['require'] ?? []);
            }
        } else {
            $dependencyIssues[] = [
                'file' => 'composer.json',
                'issue' => 'composer.json not found',
                'severity' => 'medium'
            ];
        }

        // Check package.json
        $packageJson = $this->projectPath . '/package.json';
        if (file_exists($packageJson)) {
            $package = json_decode(file_get_contents($packageJson), true);
            if ($package === null) {
                $dependencyIssues[] = [
                    'file' => 'package.json',
                    'issue' => 'Invalid JSON format',
                    'severity' => 'critical'
                ];
            } else {
                $this->stats['js_dependencies'] = count($package['dependencies'] ?? []);
            }
        }

        // Check for vendor directory
        if (!is_dir($this->projectPath . '/vendor')) {
            $dependencyIssues[] = [
                'issue' => 'vendor directory not found - run composer install',
                'severity' => 'critical'
            ];
        }

        // Check for node_modules
        if (!is_dir($this->projectPath . '/node_modules')) {
            $dependencyIssues[] = [
                'issue' => 'node_modules not found - run npm install',
                'severity' => 'medium'
            ];
        }

        $this->findings['dependency_issues'] = $dependencyIssues;

        echo "  PHP Dependencies: " . ($this->stats['php_dependencies'] ?? 0) . "\n";
        echo "  JS Dependencies: " . ($this->stats['js_dependencies'] ?? 0) . "\n";
        echo "  Dependency Issues: " . count($dependencyIssues) . "\n\n";
    }

    /**
     * Scan environment setup
     */
    private function scanEnvironmentSetup()
    {
        echo "[9/10] Scanning environment setup...\n";

        $envIssues = [];

        // Check .env file
        $envFile = $this->projectPath . '/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);

            // Check for required variables
            $requiredVars = ['DB_HOST', 'DB_DATABASE', 'APP_URL', 'APP_ENV'];
            foreach ($requiredVars as $var) {
                if (strpos($envContent, $var) === false) {
                    $envIssues[] = [
                        'variable' => $var,
                        'issue' => "Required environment variable $var not set",
                        'severity' => 'high'
                    ];
                }
            }

            // Check for production config
            if (strpos($envContent, 'production') !== false) {
                if (strpos($envContent, 'APP_DEBUG=true') !== false) {
                    $envIssues[] = [
                        'issue' => 'Debug mode enabled in production',
                        'severity' => 'critical'
                    ];
                }
            }
        } else {
            $envIssues[] = [
                'issue' => '.env file not found',
                'severity' => 'critical'
            ];
        }

        // Check directory permissions
        $criticalDirs = ['storage', 'storage/cache', 'storage/logs', 'public/uploads'];
        foreach ($criticalDirs as $dir) {
            $fullPath = $this->projectPath . '/' . $dir;
            if (is_dir($fullPath) && !is_writable($fullPath)) {
                $envIssues[] = [
                    'directory' => $dir,
                    'issue' => 'Directory not writable',
                    'severity' => 'high'
                ];
            }
        }

        $this->findings['environment_issues'] = $envIssues;

        echo "  Environment Issues: " . count($envIssues) . "\n\n";
    }

    /**
     * Generate comprehensive report
     */
    private function generateComprehensiveReport()
    {
        echo "[10/10] Generating comprehensive report...\n\n";

        $criticalIssues = 0;
        $highIssues = 0;
        $mediumIssues = 0;
        $lowIssues = 0;

        foreach ($this->findings as $category => $items) {
            foreach ($items as $item) {
                $severity = $item['severity'] ?? 'medium';
                switch ($severity) {
                    case 'critical':
                        $criticalIssues++;
                        break;
                    case 'high':
                        $highIssues++;
                        break;
                    case 'medium':
                        $mediumIssues++;
                        break;
                    case 'low':
                        $lowIssues++;
                        break;
                }
            }
        }

        echo "=== SCAN RESULTS SUMMARY ===\n";
        echo "Critical Issues: $criticalIssues\n";
        echo "High Priority Issues: $highIssues\n";
        echo "Medium Priority Issues: $mediumIssues\n";
        echo "Low Priority Issues: $lowIssues\n";
        echo "Total Issues: " . ($criticalIssues + $highIssues + $mediumIssues + $lowIssues) . "\n\n";

        $this->stats['critical_issues'] = $criticalIssues;
        $this->stats['high_issues'] = $highIssues;
        $this->stats['medium_issues'] = $mediumIssues;
        $this->stats['low_issues'] = $lowIssues;
    }

    /**
     * Save comprehensive report
     */
    public function saveReport($filename = 'deep_project_scan_report.md')
    {
        $content = "# APS Dream Home - Maximum Level Deep Project Scan Report\n\n";
        $content .= "**Scan Date:** " . date('Y-m-d H:i:s') . "\n";
        $content .= "**Project Path:** " . $this->projectPath . "\n\n";

        $content .= "## Executive Summary\n\n";
        $content .= "- **Total Files Scanned:** " . $this->stats['total_files'] . "\n";
        $content .= "- **PHP Files:** " . $this->stats['php_files'] . "\n";
        $content .= "- **Database Tables:** " . ($this->stats['database_tables'] ?? 0) . "\n";
        $content .= "- **Critical Issues:** " . $this->stats['critical_issues'] . "\n";
        $content .= "- **High Priority Issues:** " . $this->stats['high_issues'] . "\n";
        $content .= "- **Medium Priority Issues:** " . $this->stats['medium_issues'] . "\n";
        $content .= "- **Low Priority Issues:** " . $this->stats['low_issues'] . "\n\n";

        // Detailed findings
        foreach ($this->findings as $category => $items) {
            if (!empty($items)) {
                $content .= "## " . ucwords(str_replace('_', ' ', $category)) . "\n\n";

                foreach ($items as $item) {
                    $severity = strtoupper($item['severity'] ?? 'MEDIUM');
                    $content .= "### [$severity] ";

                    if (isset($item['file'])) {
                        $content .= $item['file'] . "\n\n";
                    }

                    $content .= "**Issue:** " . ($item['issue'] ?? $item['error'] ?? 'Unknown issue') . "\n\n";

                    if (isset($item['recommendation'])) {
                        $content .= "**Recommendation:** " . $item['recommendation'] . "\n\n";
                    }
                }
            }
        }

        file_put_contents($this->projectPath . '/' . $filename, $content);
        echo "Comprehensive report saved to: $filename\n";
    }

    /**
     * Helper functions
     */
    private function scanDirectory($path)
    {
        // Recursive directory scanning implementation
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (!isset($this->stats['file_types'][$ext])) {
                    $this->stats['file_types'][$ext] = 0;
                }
                $this->stats['file_types'][$ext]++;
            }
        }
    }

    private function countDirectories($path)
    {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $count++;
            }
        }

        return $count;
    }

    private function countFiles($path)
    {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }

        return $count;
    }
}

// Run deep scan if executed directly
if (php_sapi_name() === 'cli') {
    $scanner = new DeepProjectScanner(__DIR__ . '/..');
    $scanner->runDeepScan();
    $scanner->saveReport();
}
