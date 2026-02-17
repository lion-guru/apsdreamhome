<?php
/**
 * APS Dream Home - Comprehensive Deep Scan & Auto-Routing System
 * This script performs a deep scan of the project to identify all pages and create automatic routing
 */

// Define security constant
define('INCLUDED_FROM_MAIN', true);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

class ProjectDeepScanner {
    private $rootPath;
    private $scanResults = [];
    private $routeSuggestions = [];
    private $existingRoutes = [];

    public function __construct($rootPath) {
        $this->rootPath = rtrim($rootPath, '/');
    }

    /**
     * Perform comprehensive deep scan of the project
     */
    public function deepScan() {
        echo "<h1>🔍 APS Dream Home - Comprehensive Deep Scan</h1>";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;'>";
        echo "<p><strong>Starting comprehensive scan of project structure...</strong></p>";
        echo "</div>";

        // Step 1: Scan all directories and files
        $this->scanDirectories();

        // Step 2: Analyze PHP files for routing potential
        $this->analyzePhpFiles();

        // Step 3: Identify existing routing patterns
        $this->analyzeExistingRoutes();

        // Step 4: Generate route suggestions
        $this->generateRouteSuggestions();

        // Step 5: Display results
        $this->displayResults();

        return $this->scanResults;
    }

    /**
     * Scan all directories recursively
     */
    private function scanDirectories() {
        echo "<h2>📁 Scanning Directory Structure</h2>";

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $directories = [];
        $files = [];

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $directories[] = $file->getPathname();
            } else {
                $files[] = $file->getPathname();
            }
        }

        $this->scanResults['directories'] = $directories;
        $this->scanResults['files'] = $files;
        $this->scanResults['total_directories'] = count($directories);
        $this->scanResults['total_files'] = count($files);

        echo "<div class='result-box'>";
        echo "<p>✅ Found <strong>" . count($directories) . "</strong> directories and <strong>" . count($files) . "</strong> files</p>";
        echo "</div>";
    }

    /**
     * Analyze PHP files for routing potential
     */
    private function analyzePhpFiles() {
        echo "<h2>🐘 Analyzing PHP Files</h2>";

        $phpFiles = [];
        $pageCandidates = [];
        $adminFiles = [];
        $apiFiles = [];
        $authFiles = [];

        foreach ($this->scanResults['files'] as $filePath) {
            if (substr($filePath, -4) === '.php') {
                $relativePath = str_replace($this->rootPath . '/', '', $filePath);
                $phpFiles[] = $relativePath;

                // Check file content for page indicators
                $content = file_get_contents($filePath);
                $fileInfo = [
                    'path' => $relativePath,
                    'size' => filesize($filePath),
                    'is_page' => $this->isPageFile($content, $relativePath),
                    'has_html' => strpos($content, '<html') !== false || strpos($content, '<body') !== false,
                    'has_forms' => strpos($content, '<form') !== false,
                    'has_db_connection' => strpos($content, 'mysqli_connect') !== false || strpos($content, 'PDO') !== false,
                    'functions' => $this->extractFunctions($content)
                ];

                // Categorize files
                if (strpos($relativePath, 'admin/') === 0) {
                    $adminFiles[] = $fileInfo;
                } elseif (strpos($relativePath, 'api/') === 0) {
                    $apiFiles[] = $fileInfo;
                } elseif (strpos($relativePath, 'auth/') === 0) {
                    $authFiles[] = $fileInfo;
                } elseif ($fileInfo['is_page']) {
                    $pageCandidates[] = $fileInfo;
                }
            }
        }

        $this->scanResults['php_files'] = $phpFiles;
        $this->scanResults['page_candidates'] = $pageCandidates;
        $this->scanResults['admin_files'] = $adminFiles;
        $this->scanResults['api_files'] = $apiFiles;
        $this->scanResults['auth_files'] = $authFiles;

        echo "<div class='result-box'>";
        echo "<p>✅ Analyzed <strong>" . count($phpFiles) . "</strong> PHP files</p>";
        echo "<p>📄 Found <strong>" . count($pageCandidates) . "</strong> potential page files</p>";
        echo "<p>👨‍💼 Found <strong>" . count($adminFiles) . "</strong> admin files</p>";
        echo "<p>🔌 Found <strong>" . count($apiFiles) . "</strong> API files</p>";
        echo "</div>";
    }

    /**
     * Check if a PHP file is likely a page (not just an include/utility)
     */
    private function isPageFile($content, $path) {
        // Skip certain file types
        $skipPatterns = [
            'config.php', 'functions.php', 'header.php', 'footer.php', 'sidebar.php',
            'connection.php', 'database.php', 'auth.php', 'session.php',
            'includes/', 'vendor/', 'node_modules/', 'tests/', 'docs/'
        ];

        foreach ($skipPatterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                return false;
            }
        }

        // Look for page indicators
        $pageIndicators = [
            'echo.*<html', 'echo.*<body', 'echo.*<div',
            'include.*header', 'require.*header',
            '<!DOCTYPE html', '<html', '<head>', '<body>',
            'echo.*Welcome', 'echo.*Dashboard', 'echo.*Login',
            '$_SESSION', 'session_start()',
            'if.*isset.*POST', 'if.*isset.*GET'
        ];

        $score = 0;
        foreach ($pageIndicators as $indicator) {
            if (preg_match('/' . $indicator . '/i', $content)) {
                $score++;
            }
        }

        return $score >= 2;
    }

    /**
     * Extract function definitions from PHP content
     */
    private function extractFunctions($content) {
        $functions = [];
        preg_match_all('/function\s+(\w+)\s*\(/', $content, $matches);
        if (isset($matches[1])) {
            $functions = $matches[1];
        }
        return $functions;
    }

    /**
     * Analyze existing routing configuration
     */
    private function analyzeExistingRoutes() {
        echo "<h2>🛣️ Analyzing Existing Routes</h2>";

        if (file_exists($this->rootPath . '/router.php')) {
            $routerContent = file_get_contents($this->rootPath . '/router.php');
            preg_match_all('/\'([^\']+)\'\s*=>\s*\'([^\']+)\'/', $routerContent, $matches);

            if (isset($matches[1]) && isset($matches[2])) {
                $this->existingRoutes = array_combine($matches[1], $matches[2]);
            }
        }

        $htaccessContent = file_get_contents($this->rootPath . '/.htaccess');

        $this->scanResults['existing_routes'] = $this->existingRoutes;
        $this->scanResults['htaccess_has_routing'] = strpos($htaccessContent, 'router.php') !== false;
        $this->scanResults['total_existing_routes'] = count($this->existingRoutes);

        echo "<div class='result-box'>";
        echo "<p>✅ Found <strong>" . count($this->existingRoutes) . "</strong> existing routes</p>";
        echo "<p>" . ($this->scanResults['htaccess_has_routing'] ? '✅' : '❌') . " .htaccess routing: " .
             ($this->scanResults['htaccess_has_routing'] ? 'Enabled' : 'Disabled') . "</p>";
        echo "</div>";
    }

    /**
     * Generate route suggestions for unhandled pages
     */
    private function generateRouteSuggestions() {
        echo "<h2>💡 Generating Route Suggestions</h2>";

        $suggestions = [];
        $unhandledPages = [];

        foreach ($this->scanResults['page_candidates'] as $file) {
            $relativePath = $file['path'];
            $routePath = $this->generateRoutePath($relativePath);

            if (!isset($this->existingRoutes[$routePath]) && !isset($this->existingRoutes[$relativePath])) {
                $suggestions[] = [
                    'route' => $routePath,
                    'file' => $relativePath,
                    'type' => $this->categorizeRoute($relativePath),
                    'priority' => $this->calculateRoutePriority($file, $relativePath)
                ];
                $unhandledPages[] = $relativePath;
            }
        }

        // Sort by priority (higher first)
        usort($suggestions, function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        $this->routeSuggestions = $suggestions;
        $this->scanResults['unhandled_pages'] = $unhandledPages;
        $this->scanResults['route_suggestions'] = $suggestions;

        echo "<div class='result-box'>";
        echo "<p>✅ Generated <strong>" . count($suggestions) . "</strong> route suggestions</p>";
        echo "<p>📄 Found <strong>" . count($unhandledPages) . "</strong> unhandled pages</p>";
        echo "</div>";
    }

    /**
     * Generate a route path from file path
     */
    private function generateRoutePath($filePath) {
        // Remove .php extension
        $route = preg_replace('/\.php$/', '', $filePath);

        // Remove directory prefixes that shouldn't be in routes
        $route = preg_replace('/^(admin|api|auth|user)\//', '', $route);

        // Handle special cases
        if ($route === 'index') {
            return '';
        }

        return $route;
    }

    /**
     * Categorize route type
     */
    private function categorizeRoute($path) {
        if (strpos($path, 'admin/') === 0) return 'admin';
        if (strpos($path, 'api/') === 0) return 'api';
        if (strpos($path, 'auth/') === 0) return 'auth';
        if (strpos($path, 'user/') === 0) return 'user';
        return 'public';
    }

    /**
     * Calculate route priority based on file characteristics
     */
    private function calculateRoutePriority($file, $path) {
        $priority = 0;

        // Higher priority for files with HTML content
        if ($file['has_html']) $priority += 3;

        // Higher priority for larger files (likely complete pages)
        if ($file['size'] > 10000) $priority += 2;

        // Higher priority for files with forms (interactive pages)
        if ($file['has_forms']) $priority += 2;

        // Higher priority for dashboard-like files
        if (strpos($path, 'dashboard') !== false) $priority += 2;

        // Lower priority for test files
        if (strpos($path, 'test') !== false) $priority -= 2;

        return $priority;
    }

    /**
     * Display comprehensive scan results
     */
    private function displayResults() {
        echo "<h2>📊 Comprehensive Scan Results</h2>";

        // Project Overview
        echo "<div class='section'>";
        echo "<h3>📈 Project Overview</h3>";
        echo "<div class='stats-grid'>";
        echo "<div class='stat-item'>";
        echo "<span class='stat-number'>" . $this->scanResults['total_directories'] . "</span>";
        echo "<span class='stat-label'>Directories</span>";
        echo "</div>";
        echo "<div class='stat-item'>";
        echo "<span class='stat-number'>" . $this->scanResults['total_files'] . "</span>";
        echo "<span class='stat-label'>Total Files</span>";
        echo "</div>";
        echo "<div class='stat-item'>";
        echo "<span class='stat-number'>" . count($this->scanResults['php_files']) . "</span>";
        echo "<span class='stat-label'>PHP Files</span>";
        echo "</div>";
        echo "<div class='stat-item'>";
        echo "<span class='stat-number'>" . count($this->scanResults['page_candidates']) . "</span>";
        echo "<span class='stat-label'>Page Candidates</span>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        // Route Suggestions
        if (!empty($this->routeSuggestions)) {
            echo "<div class='section'>";
            echo "<h3>🚀 Route Suggestions</h3>";
            echo "<p>Add these routes to your router.php file:</p>";
            echo "<div class='code-block'>";
            echo "'routes' => [<br>";
            foreach ($this->routeSuggestions as $index => $suggestion) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;'{$suggestion['route']}' => '{$suggestion['file']}',<br>";
                if ($index >= 19) { // Show first 20 suggestions
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;// ... and more<br>";
                    break;
                }
            }
            echo "],<br>";
            echo "</div>";
            echo "</div>";
        }

        // Generate Auto-Routing Code
        echo "<div class='section'>";
        echo "<h3>⚡ Auto-Routing System</h3>";
        echo "<p>Copy this enhanced routing logic to your router.php:</p>";
        echo "<div class='code-block'>";
        echo $this->generateAutoRoutingCode();
        echo "</div>";
        echo "</div>";

        // Issues and Recommendations
        echo "<div class='section'>";
        echo "<h3>🔧 Issues & Recommendations</h3>";
        $issues = $this->identifyIssues();
        if (!empty($issues)) {
            echo "<ul>";
            foreach ($issues as $issue) {
                echo "<li>{$issue}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='success'>✅ No critical issues found!</p>";
        }
        echo "</div>";
    }

    /**
     * Generate enhanced auto-routing code
     */
    private function generateAutoRoutingCode() {
        return <<<'PHP'
/**
 * Enhanced Auto-Routing System
 * Automatically handles page requests based on file structure
 */

// Enhanced routing with auto-discovery
function enhancedAutoRouting($request_uri, $routes) {
    // First check explicit routes
    if (isset($routes[$request_uri])) {
        $route = $routes[$request_uri];
        if (file_exists($route)) {
            return $route;
        }
    }

    // Auto-discover pages in root directory
    $possibleFiles = [
        $request_uri . '.php',
        strtolower($request_uri) . '.php',
        str_replace('-', '_', $request_uri) . '.php'
    ];

    foreach ($possibleFiles as $file) {
        if (file_exists($file)) {
            return $file;
        }
    }

    // Check in subdirectories
    $subdirectories = ['pages', 'public'];
    foreach ($subdirectories as $subdir) {
        $filePath = $subdir . '/' . $request_uri . '.php';
        if (file_exists($filePath)) {
            return $filePath;
        }
    }

    return false;
}

// Use enhanced routing
$file_path = enhancedAutoRouting($request_uri, $routes);
if ($file_path) {
    require_once $file_path;
    exit();
}
PHP;
    }

    /**
     * Identify potential issues
     */
    private function identifyIssues() {
        $issues = [];

        if (!$this->scanResults['htaccess_has_routing']) {
            $issues[] = "❌ .htaccess routing is not properly configured";
        }

        if (empty($this->existingRoutes)) {
            $issues[] = "⚠️ No routes defined in router.php";
        }

        $unhandledCount = count($this->scanResults['unhandled_pages']);
        if ($unhandledCount > 10) {
            $issues[] = "⚠️ {$unhandledCount} pages are not handled by current routing";
        }

        return $issues;
    }
}

// CSS for better display
echo "<style>
.section { margin: 30px 0; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.section h3 { color: #007bff; margin-top: 0; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin: 20px 0; }
.stat-item { text-align: center; padding: 15px; background: #f8f9fa; border-radius: 5px; }
.stat-number { display: block; font-size: 2em; font-weight: bold; color: #007bff; }
.stat-label { color: #666; font-size: 0.9em; }
.code-block { background: #2d3748; color: #e2e8f0; padding: 20px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto; }
.success { color: #28a745; font-weight: bold; }
.result-box { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0; }
</style>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deep Scan Results - APS Dream Home</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 1200px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <?php
        $scanner = new ProjectDeepScanner(__DIR__);
        $scanner->deepScan();
        ?>
    </div>
</body>
</html>
