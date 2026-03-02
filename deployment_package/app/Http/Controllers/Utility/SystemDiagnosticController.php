<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Admin\AdminController;
use Exception;

/**
 * SystemDiagnosticController
 *
 * Comprehensive system health check and diagnostic tool
 */
class SystemDiagnosticController extends AdminController
{
    private $report = [];
    private $criticalIssues = [];

    public function __construct()
    {
        parent::__construct();
        // AdminController handles role:admin check
    }

    public function index()
    {
        $this->runDiagnostic();

        // In a real MVC, we would return a view.
        // For now, let's output JSON or a simple HTML report
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => empty($this->criticalIssues) ? 'success' : 'error',
                'report' => $this->report,
                'critical_issues' => $this->criticalIssues
            ]);
            exit;
        }

        return $this->renderDiagnosticReport();
    }

    private function runDiagnostic()
    {
        $this->checkPhpConfiguration();
        $this->checkExtensions();
        $this->checkDatabaseConnection();
        $this->checkSessionConfiguration();
        $this->checkFilePermissions();
        $this->checkCriticalFiles();
    }

    private function checkPhpConfiguration()
    {
        $phpVersion = phpversion();
        $phpCheck = version_compare($phpVersion, '7.4.0', '>=');

        $this->report['php'] = [
            'version' => $phpVersion,
            'status' => $phpCheck,
            'message' => $phpCheck ? 'OK' : 'PHP 7.4 or higher is required'
        ];

        if (!$phpCheck) {
            $this->criticalIssues[] = "Incompatible PHP Version: $phpVersion";
        }
    }

    private function checkExtensions()
    {
        $requiredExtensions = ['pdo_mysql', 'mysqli', 'json', 'session', 'gd', 'mbstring', 'openssl'];
        $extensionStatus = [];

        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $extensionStatus[$ext] = $loaded;
            if (!$loaded) {
                $this->criticalIssues[] = "Missing PHP Extension: $ext";
            }
        }

        $this->report['extensions'] = $extensionStatus;
    }

    private function checkDatabaseConnection()
    {
        try {
            // App\Core\Database doesn't have ping(), check connection directly
            $dbCheck = $this->db && $this->db->getConnection();

            if ($dbCheck) {
                $requiredTables = ['users', 'properties', 'customers', 'leads', 'property_visits', 'notifications'];
                $tableStatus = [];

                $result = $this->db->query("SHOW TABLES");
                $tables = [];
                if ($result instanceof \PDOStatement) {
                    while ($row = $result->fetch(\PDO::FETCH_NUM)) {
                        $tables[] = $row[0];
                    }
                }

                foreach ($requiredTables as $table) {
                    $exists = in_array($table, $tables);
                    $tableStatus[$table] = $exists;
                    if (!$exists) {
                        $this->criticalIssues[] = "Missing Required Table: $table";
                    }
                }

                $this->report['database'] = [
                    'connected' => true,
                    'tables' => $tableStatus
                ];
            } else {
                throw new Exception("Database connection failed");
            }
        } catch (Exception $e) {
            $this->report['database'] = [
                'connected' => false,
                'error' => $e->getMessage()
            ];
            $this->criticalIssues[] = "Database Connection Error: " . $e->getMessage();
        }
    }

    private function checkSessionConfiguration()
    {
        $sessionStatus = session_status() === PHP_SESSION_ACTIVE;
        $this->report['session'] = [
            'active' => $sessionStatus,
            'save_path' => ini_get('session.save_path'),
            'cookie_httponly' => ini_get('session.cookie_httponly'),
            'cookie_secure' => ini_get('session.cookie_secure'),
            'gc_maxlifetime' => ini_get('session.gc_maxlifetime')
        ];

        if (!$sessionStatus) {
            $this->criticalIssues[] = "Session Handling is not active";
        }
    }

    private function checkFilePermissions()
    {
        $baseDir = dirname(__DIR__, 3);
        $writableDirs = [
            $baseDir . '/public/uploads',
            $baseDir . '/storage/cache',
            $baseDir . '/storage/logs'
        ];

        $dirStatus = [];
        foreach ($writableDirs as $dir) {
            if (file_exists($dir)) {
                $writable = is_writable($dir);
                $dirStatus[$dir] = [
                    'exists' => true,
                    'writable' => $writable
                ];
                if (!$writable) {
                    $this->criticalIssues[] = "Directory not writable: $dir";
                }
            } else {
                $dirStatus[$dir] = [
                    'exists' => false,
                    'writable' => false
                ];
                // We don't always consider missing upload dirs as critical if they can be created
            }
        }

        $this->report['permissions'] = $dirStatus;
    }

    private function checkCriticalFiles()
    {
        $baseDir = dirname(__DIR__, 3);
        $criticalFiles = [
            '.env',
            'public/index.php',
            'config/database.php'
        ];

        $fileStatus = [];
        foreach ($criticalFiles as $file) {
            $exists = file_exists($baseDir . DIRECTORY_SEPARATOR . $file);
            $fileStatus[$file] = $exists;
            if (!$exists) {
                $this->criticalIssues[] = "Missing Critical File: $file";
            }
        }

        $this->report['critical_files'] = $fileStatus;
    }

    private function renderDiagnosticReport()
    {
        // For simplicity, we output directly if view is not available
        // In a real app, this would be $this->view('admin/diagnostic', $data);

        $statusClass = empty($this->criticalIssues) ? 'success' : 'error';
        $statusText = empty($this->criticalIssues) ? 'All Systems Operational' : 'Issues Detected';

        ob_start();
?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>System Diagnostic Report</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 1000px;
                    margin: 0 auto;
                    padding: 20px;
                    background: #f4f7f6;
                }

                .card {
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                    margin-bottom: 20px;
                }

                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 2px solid #eee;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }

                .status-badge {
                    padding: 5px 15px;
                    border-radius: 20px;
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 0.8em;
                }

                .success {
                    background: #d4edda;
                    color: #155724;
                }

                .error {
                    background: #f8d7da;
                    color: #721c24;
                }

                .item {
                    margin-bottom: 10px;
                    padding: 10px;
                    border-left: 4px solid #eee;
                    background: #fafafa;
                }

                .item.ok {
                    border-left-color: #28a745;
                }

                .item.fail {
                    border-left-color: #dc3545;
                }

                h2 {
                    color: #2c3e50;
                    margin-top: 0;
                }

                pre {
                    background: #f8f9fa;
                    padding: 10px;
                    border-radius: 4px;
                    overflow-x: auto;
                }
            </style>
        </head>

        <body>
            <div class="card">
                <div class="header">
                    <h1>System Diagnostic</h1>
                    <span class="status-badge <?php echo h($statusClass); ?>"><?php echo h($statusText); ?></span>
                </div>

                <?php if (!empty($this->criticalIssues)): ?>
                    <div class="card error">
                        <h2>Critical Issues</h2>
                        <ul>
                            <?php foreach ($this->criticalIssues as $issue): ?>
                                <li><?php echo h($issue); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="grid">
                    <section>
                        <h2>Environment</h2>
                        <div class="item <?php echo $this->report['php']['status'] ? 'ok' : 'fail'; ?>">
                            <strong>PHP Version:</strong> <?php echo h($this->report['php']['version']); ?>
                        </div>
                        <h3>Extensions</h3>
                        <?php foreach ($this->report['extensions'] as $ext => $loaded): ?>
                            <div class="item <?php echo $loaded ? 'ok' : 'fail'; ?>">
                                <strong><?php echo h($ext); ?>:</strong> <?php echo $loaded ? 'Loaded' : 'Missing'; ?>
                            </div>
                        <?php endforeach; ?>
                    </section>

                    <section>
                        <h2>Database</h2>
                        <div class="item <?php echo $this->report['database']['connected'] ? 'ok' : 'fail'; ?>">
                            <strong>Connection:</strong> <?php echo $this->report['database']['connected'] ? 'Successful' : 'Failed'; ?>
                        </div>
                        <?php if ($this->report['database']['connected']): ?>
                            <h3>Tables</h3>
                            <?php foreach ($this->report['database']['tables'] as $table => $exists): ?>
                                <div class="item <?php echo $exists ? 'ok' : 'fail'; ?>">
                                    <strong><?php echo h($table); ?>:</strong> <?php echo $exists ? 'Exists' : 'Missing'; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </body>

        </html>
<?php
        return ob_get_clean();
    }
}
