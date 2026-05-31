<?php
/**
 * Project Health Monitor
 */
class ProjectHealthMonitor {
    private $basePath;
    private $config;
    
    public function __construct() {
        $this->basePath = __DIR__;
        $this->config = [
            'db' => [
                'host' => '127.0.0.1',
                'port' => '3307',
                'dbname' => 'apsdreamhome',
                'username' => 'root',
                'password' => ''
            ],
            'paths' => [
                'controllers' => __DIR__ . '/app/Http/Controllers',
                'models' => __DIR__ . '/app/Models',
                'views' => __DIR__ . '/app/views',
                'routes' => __DIR__ . '/routes'
            ]
        ];
    }
    
    public function checkDatabase() {
        try {
            $dsn = "mysql:host={$this->config['db']['host']};port={$this->config['db']['port']};dbname={$this->config['db']['dbname']}";
            $pdo = new PDO($dsn, $this->config['db']['username'], $this->config['db']['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return [
                'status' => 'ok',
                'tables' => count($tables),
                'message' => 'Database connected successfully'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function checkStructure() {
        $checks = [];
        
        foreach ($this->config['paths'] as $name => $path) {
            $checks[$name] = file_exists($path) ? 'ok' : 'missing';
        }
        
        return $checks;
    }
    
    public function getFullReport() {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $this->checkDatabase(),
            'structure' => $this->checkStructure(),
            'php_errors' => [
                'controllers' => count(glob($this->config['paths']['controllers'] . '/*.php')),
                'models' => count(glob($this->config['paths']['models'] . '/*.php'))
            ]
        ];
    }
}

$monitor = new ProjectHealthMonitor();
$report = $monitor->getFullReport();

header('Content-Type: application/json');
echo json_encode($report, JSON_PRETTY_PRINT);
