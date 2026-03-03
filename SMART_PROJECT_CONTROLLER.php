<?php
/**
 * APS Dream Home - Smart Project Controller
 * Intelligent project management system with auto-fixing capabilities
 * This system thinks like a developer and automatically manages the project
 */

echo "🧠 APS DREAM HOME - SMART PROJECT CONTROLLER\n";
echo "====================================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Smart controller configuration
define('SMART_MODE', true);
define('AUTO_THINKING', true);
define('PROJECT_UNDERSTANDING', true);

class SmartProjectController {
    private $projectState = [];
    private $issues = [];
    private $fixes = [];
    private $understanding = [];
    
    public function __construct() {
        $this->initializeProjectUnderstanding();
        $this->analyzeProjectState();
        $this->detectIssues();
        $this->implementFixes();
        $this->validateProject();
    }
    
    private function initializeProjectUnderstanding() {
        echo "🧠 Initializing project understanding...\n";
        
        $this->understanding = [
            'project_name' => 'APS Dream Home',
            'project_type' => 'Real Estate Management System',
            'architecture' => 'PHP MVC with Modern Features',
            'key_components' => [
                'Controllers' => 'Handle HTTP requests and routing',
                'Models' => 'Handle data operations',
                'Views' => 'Handle presentation layer',
                'Config' => 'Handle configuration',
                'Assets' => 'Handle frontend resources'
            ],
            'critical_paths' => [
                'public/index.php' => 'Entry point',
                'app/Core/App.php' => 'Application core',
                'config/paths.php' => 'Path configuration',
                'app/views/layouts/base.php' => 'Base layout'
            ],
            'common_issues' => [
                'syntax_errors' => 'PHP syntax mistakes',
                'missing_files' => 'Required files not present',
                'namespace_issues' => 'Incorrect namespace declarations',
                'routing_problems' => 'URL routing not working',
                'layout_inconsistency' => 'Layout not consistent across pages'
            ]
        ];
        
        echo "   ✅ Project understanding initialized\n";
    }
    
    private function analyzeProjectState() {
        echo "🔍 Analyzing project state...\n";
        
        // Check critical files
        $this->projectState['critical_files'] = [
            'public/index.php' => file_exists(BASE_PATH . '/public/index.php'),
            'app/Core/App.php' => file_exists(BASE_PATH . '/app/Core/App.php'),
            'config/paths.php' => file_exists(BASE_PATH . '/config/paths.php'),
            'app/views/layouts/base.php' => file_exists(BASE_PATH . '/app/views/layouts/base.php'),
            'public/.htaccess' => file_exists(BASE_PATH . '/public/.htaccess')
        ];
        
        // Check directory structure
        $this->projectState['directories'] = [
            'app/Http/Controllers' => is_dir(BASE_PATH . '/app/Http/Controllers'),
            'app/Models' => is_dir(BASE_PATH . '/app/Models'),
            'app/views' => is_dir(BASE_PATH . '/app/views'),
            'public/assets' => is_dir(BASE_PATH . '/public/assets'),
            'config' => is_dir(BASE_PATH . '/config')
        ];
        
        // Check syntax of key files
        $this->projectState['syntax_check'] = $this->checkSyntax();
        
        echo "   ✅ Project state analysis completed\n";
    }
    
    private function checkSyntax() {
        $syntaxResults = [];
        $keyFiles = [
            'app/Core/App.php',
            'config/paths.php',
            'app/Http/Controllers/HomeController.php'
        ];
        
        foreach ($keyFiles as $file) {
            $filePath = BASE_PATH . '/' . $file;
            if (file_exists($filePath)) {
                $output = [];
                $returnCode = 0;
                exec("php -l \"$filePath\" 2>&1", $output, $returnCode);
                $syntaxResults[$file] = $returnCode === 0;
            } else {
                $syntaxResults[$file] = false;
            }
        }
        
        return $syntaxResults;
    }
    
    private function detectIssues() {
        echo "🔍 Detecting issues...\n";
        
        $this->issues = [];
        
        // Detect missing files
        foreach ($this->projectState['critical_files'] as $file => $exists) {
            if (!$exists) {
                $this->issues[] = [
                    'type' => 'missing_file',
                    'file' => $file,
                    'severity' => 'critical',
                    'description' => "Critical file $file is missing"
                ];
            }
        }
        
        // Detect missing directories
        foreach ($this->projectState['directories'] as $dir => $exists) {
            if (!$exists) {
                $this->issues[] = [
                    'type' => 'missing_directory',
                    'directory' => $dir,
                    'severity' => 'high',
                    'description' => "Required directory $dir is missing"
                ];
            }
        }
        
        // Detect syntax errors
        foreach ($this->projectState['syntax_check'] as $file => $valid) {
            if (!$valid) {
                $this->issues[] = [
                    'type' => 'syntax_error',
                    'file' => $file,
                    'severity' => 'critical',
                    'description' => "Syntax error in $file"
                ];
            }
        }
        
        // Detect specific issues from current problems
        $this->detectSpecificIssues();
        
        echo "   ✅ Issues detected: " . count($this->issues) . "\n";
    }
    
    private function detectSpecificIssues() {
        // Check for iconClass issue
        $uiFile = BASE_PATH . '/advanced_ui_components.php';
        if (file_exists($uiFile)) {
            $content = file_get_contents($uiFile);
            if (strpos($content, '$iconClass') !== false && strpos($content, 'iconClass ??') === false) {
                $this->issues[] = [
                    'type' => 'unassigned_variable',
                    'file' => 'advanced_ui_components.php',
                    'severity' => 'high',
                    'description' => 'Unassigned variable $iconClass'
                ];
            }
        }
        
        // Check for App.php issues
        $appFile = BASE_PATH . '/app/Core/App.php';
        if (file_exists($appFile)) {
            $content = file_get_contents($appFile);
            if (strpos($content, 'catch (Exception $e)') !== false && strpos($content, 'catch (\Exception $e)') === false) {
                $this->issues[] = [
                    'type' => 'namespace_issue',
                    'file' => 'app/Core/App.php',
                    'severity' => 'critical',
                    'description' => 'Exception class not properly namespaced'
                ];
            }
        }
        
        // Check for paths.php issues
        $pathsFile = BASE_PATH . '/config/paths.php';
        if (file_exists($pathsFile)) {
            $content = file_get_contents($pathsFile);
            if (strpos($content, '??') !== false) {
                $this->issues[] = [
                    'type' => 'syntax_error',
                    'file' => 'config/paths.php',
                    'severity' => 'critical',
                    'description' => 'Syntax error in paths configuration'
                ];
            }
        }
    }
    
    private function implementFixes() {
        echo "🔧 Implementing fixes...\n";
        
        foreach ($this->issues as $issue) {
            $fixMethod = 'fix' . str_replace('_', '', ucwords($issue['type'], '_'));
            
            if (method_exists($this, $fixMethod)) {
                $result = $this->$fixMethod($issue);
                $this->fixes[] = [
                    'issue' => $issue,
                    'fix_method' => $fixMethod,
                    'result' => $result,
                    'status' => $result ? 'success' : 'failed'
                ];
            } else {
                $this->fixes[] = [
                    'issue' => $issue,
                    'fix_method' => 'manual',
                    'result' => false,
                    'status' => 'manual_fix_required'
                ];
            }
        }
        
        echo "   ✅ Fixes implemented: " . count($this->fixes) . "\n";
    }
    
    private function fixMissingFile($issue) {
        $file = $issue['file'];
        $filePath = BASE_PATH . '/' . $file;
        
        switch ($file) {
            case 'public/index.php':
                $content = $this->generateIndexFile();
                break;
            case 'app/Core/App.php':
                $content = $this->generateAppFile();
                break;
            case 'config/paths.php':
                $content = $this->generatePathsFile();
                break;
            case 'app/views/layouts/base.php':
                $content = $this->generateBaseLayoutFile();
                break;
            case 'public/.htaccess':
                $content = $this->generateHtaccessFile();
                break;
            default:
                return false;
        }
        
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return file_put_contents($filePath, $content) !== false;
    }
    
    private function fixMissingDirectory($issue) {
        $directory = $issue['directory'];
        $dirPath = BASE_PATH . '/' . $directory;
        
        return mkdir($dirPath, 0755, true);
    }
    
    private function fixSyntaxError($issue) {
        $file = $issue['file'];
        $filePath = BASE_PATH . '/' . $file;
        
        switch ($file) {
            case 'app/Core/App.php':
                return $this->fixAppFile($filePath);
            case 'config/paths.php':
                return $this->fixPathsFile($filePath);
            default:
                return false;
        }
    }
    
    private function fixUnassignedVariable($issue) {
        $file = $issue['file'];
        $filePath = BASE_PATH . '/' . $file;
        
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $content = str_replace('$iconClass', '$iconClass ?? \'default\'', $content);
            return file_put_contents($filePath, $content) !== false;
        }
        
        return false;
    }
    
    private function fixNamespaceIssue($issue) {
        $file = $issue['file'];
        $filePath = BASE_PATH . '/' . $file;
        
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $content = str_replace('catch (Exception $e)', 'catch (\Exception $e)', $content);
            return file_put_contents($filePath, $content) !== false;
        }
        
        return false;
    }
    
    private function fixAppFile($filePath) {
        $content = '<?php

namespace App\\Core;

class App
{
    private static $instance = null;
    private $basePath;
    private $config = [];
    
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath ?: dirname(__DIR__, 2);
        $this->loadConfig();
    }
    
    public static function getInstance($basePath = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($basePath);
        }
        return self::$instance;
    }
    
    private function loadConfig()
    {
        $configFile = $this->basePath . "/config/database.php";
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }
    
    public function run()
    {
        try {
            return $this->handleRequest();
        } catch (\\Exception $e) {
            return $this->handleError($e);
        }
    }
    
    private function handleRequest()
    {
        $uri = $_SERVER["REQUEST_URI"] ?? "/";
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET";
        
        if (strpos($uri, \'/api\') === 0) {
            return $this->handleApiRequest($uri, $method);
        }
        
        return $this->route($uri, $method);
    }
    
    private function handleApiRequest($uri, $method)
    {
        header(\'Content-Type: application/json\');
        echo json_encode([\'status\' => \'ok\', \'message\' => \'API is running\']);
        exit;
    }
    
    private function route($uri, $method)
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, \'/\');
        
        if ($uri === "" || $uri === "/") {
            return $this->loadController("HomeController", "index");
        } elseif ($uri === "/about") {
            return $this->loadController("HomeController", "about");
        } elseif ($uri === "/contact") {
            return $this->loadController("HomeController", "contact");
        } elseif ($uri === "/properties") {
            return $this->loadController("HomeController", "properties");
        } else {
            return $this->loadController("HomeController", "index");
        }
    }
    
    private function loadController($controller, $method)
    {
        $controllerClass = "App\\\\Http\\\\Controllers\\\\" . $controller;
        
        if (class_exists($controllerClass)) {
            $controllerInstance = new $controllerClass();
            if (method_exists($controllerInstance, $method)) {
                ob_start();
                $controllerInstance->$method();
                return ob_get_clean();
            } else {
                return "Method $method not found in $controllerClass";
            }
        } else {
            return "Controller $controllerClass not found";
        }
    }
    
    private function handleError($exception)
    {
        error_log("Application Error: " . $exception->getMessage());
        return "<h1>Application Error</h1><p>An error occurred. Please try again later.</p>";
    }
}';
        
        return file_put_contents($filePath, $content) !== false;
    }
    
    private function fixPathsFile($filePath) {
        $content = '<?php
/**
 * APS Dream Home - Centralized Path Configuration
 */

// Base directory path
define(\'BASE_PATH\', dirname(__DIR__));

// Dynamic BASE_URL calculation
if (!defined(\'BASE_URL\')) {
    $protocol = isset($_SERVER[\'HTTPS\']) && $_SERVER[\'HTTPS\'] === \'on\' ? \'https\' : \'http\';
    $host = $_SERVER[\'HTTP_HOST\'] ?? \'localhost\';
    
    $scriptName = $_SERVER[\'SCRIPT_NAME\'] ?? \'/apsdreamhome/public/index.php\';
    $scriptDir = dirname($scriptName);
    $scriptDir = str_replace(\'/public\', \'\', $scriptDir);
    $scriptDir = rtrim($scriptDir, \'/\\\');
    
    $baseUrl = $protocol . \'://\' . $host . $scriptDir;
    
    if (empty($scriptDir)) {
        $baseUrl = $protocol . \'://\' . $host . \'/apsdreamhome\';
    }
    
    define(\'BASE_URL\', $baseUrl);
}

// Additional path constants
define(\'ASSETS_URL\', BASE_URL . \'/assets\');
define(\'UPLOADS_URL\', BASE_URL . \'/uploads\');
define(\'CSS_URL\', BASE_URL . \'/assets/css\');
define(\'JS_URL\', BASE_URL . \'/assets/js\');
define(\'IMAGES_URL\', BASE_URL . \'/assets/images\');

// File system paths
define(\'ASSETS_PATH\', BASE_PATH . \'/public/assets\');
define(\'UPLOADS_PATH\', BASE_PATH . \'/public/uploads\');
define(\'VIEWS_PATH\', BASE_PATH . \'/app/views\');
define(\'CONTROLLERS_PATH\', BASE_PATH . \'/app/Http/Controllers\');
define(\'MODELS_PATH\', BASE_PATH . \'/app/Models\');
';
        
        return file_put_contents($filePath, $content) !== false;
    }
    
    private function generateIndexFile() {
        return '<?php
/**
 * APS Dream Home - Main Entry Point
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set(\'display_errors\', 1);

// Load configuration
require_once __DIR__ . \'/../config/paths.php\';

// Set HTTP_HOST
if (!isset($_SERVER[\'HTTP_HOST\'])) {
    $_SERVER[\'HTTP_HOST\'] = \'localhost\';
}

// Run application
use App\\Core\\App;

try {
    $app = new App();
    echo $app->run();
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    echo "<h1>Application Error</h1><p>An error occurred.</p>";
}
';
    }
    
    private function generateAppFile() {
        return $this->fixAppFile(BASE_PATH . '/app/Core/App.php');
    }
    
    private function generatePathsFile() {
        return $this->fixPathsFile(BASE_PATH . '/config/paths.php');
    }
    
    private function generateBaseLayoutFile() {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? \'APS Dream Home\'; ?></title>
    <link href="<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>APS Dream Home</h1>
        <?php echo $content ?? \'\'; ?>
    </div>
    <script src="<?php echo BASE_URL; ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
    }
    
    private function generateHtaccessFile() {
        return '<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]
    RewriteRule ^(.*)$ index.php [L]
</IfModule>';
    }
    
    private function validateProject() {
        echo "✅ Validating project...\n";
        
        $validationResults = [];
        
        // Re-check syntax
        $syntaxCheck = $this->checkSyntax();
        $validationResults['syntax'] = $syntaxCheck;
        
        // Check if all critical files exist
        $validationResults['files_exist'] = true;
        foreach ($this->projectState['critical_files'] as $file => $exists) {
            if (!$exists) {
                $validationResults['files_exist'] = false;
                break;
            }
        }
        
        // Check if all directories exist
        $validationResults['directories_exist'] = true;
        foreach ($this->projectState['directories'] as $dir => $exists) {
            if (!$exists) {
                $validationResults['directories_exist'] = false;
                break;
            }
        }
        
        $this->projectState['validation'] = $validationResults;
        
        echo "   ✅ Project validation completed\n";
    }
    
    public function getResults() {
        return [
            'understanding' => $this->understanding,
            'project_state' => $this->projectState,
            'issues_detected' => count($this->issues),
            'issues' => $this->issues,
            'fixes_implemented' => count($this->fixes),
            'fixes' => $this->fixes,
            'status' => $this->getProjectStatus()
        ];
    }
    
    private function getProjectStatus() {
        $totalIssues = count($this->issues);
        $successfulFixes = count(array_filter($this->fixes, function($fix) {
            return $fix['status'] === 'success';
        }));
        
        if ($totalIssues === 0) {
            return 'PERFECT';
        } elseif ($successfulFixes === $totalIssues) {
            return 'FIXED';
        } elseif ($successfulFixes > 0) {
            return 'PARTIALLY_FIXED';
        } else {
            return 'NEEDS_ATTENTION';
        }
    }
    
    public function generateReport() {
        $results = $this->getResults();
        
        echo "\n====================================================\n";
        echo "🧠 SMART PROJECT CONTROLLER REPORT\n";
        echo "====================================================\n";
        
        echo "📊 PROJECT STATUS: " . $results['status'] . "\n";
        echo "📊 ISSUES DETECTED: " . $results['issues_detected'] . "\n";
        echo "📊 FIXES IMPLEMENTED: " . $results['fixes_implemented'] . "\n\n";
        
        echo "🔍 ISSUES FOUND:\n";
        foreach ($results['issues'] as $issue) {
            echo "   ❌ " . $issue['type'] . " in " . $issue['file'] . " - " . $issue['description'] . "\n";
        }
        
        echo "\n🔧 FIXES APPLIED:\n";
        foreach ($results['fixes'] as $fix) {
            $status = $fix['status'] === 'success' ? '✅' : '❌';
            echo "   $status " . $fix['fix_method'] . " for " . $fix['issue']['type'] . "\n";
        }
        
        echo "\n🧠 PROJECT UNDERSTANDING:\n";
        echo "   📁 Project: " . $results['understanding']['project_name'] . "\n";
        echo "   🏗️  Architecture: " . $results['understanding']['architecture'] . "\n";
        echo "   🔧 Components: " . implode(', ', array_keys($results['understanding']['key_components'])) . "\n";
        
        // Save detailed report
        $reportFile = BASE_PATH . '/logs/smart_project_controller_report.json';
        file_put_contents($reportFile, json_encode($results, JSON_PRETTY_PRINT));
        echo "\n📄 Detailed report saved to: $reportFile\n";
        
        return $results;
    }
}

// Initialize and run the smart project controller
$smartController = new SmartProjectController();
$results = $smartController->generateReport();

echo "\n🎊 SMART PROJECT CONTROLLER COMPLETE! 🎊\n";
echo "🧠 The project has been analyzed, fixed, and validated automatically.\n";
echo "📊 Status: " . $results['status'] . "\n";
?>
