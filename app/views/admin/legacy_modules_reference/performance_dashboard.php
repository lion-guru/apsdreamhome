<?php
/**
 * APS Dream Home - Performance Dashboard
 * 
 * NOTE: This file uses intentional early exit for security.
 * The "unreachable code" warnings are expected and intentional.
 * 
 * Security Pattern:
 * 1. Check admin authentication
 * 2. If not authenticated, redirect and exit immediately
 * 3. Only authenticated users can access the rest of the code
 * 
 * This pattern provides proper security access control.
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/performance_monitor.php';

// Admin check - redirect if not logged in
if (!isAdmin()) {
    header('Location: index.php');
    exit; // INTENTIONAL EARLY EXIT - Security requirement
}

// Only proceed if admin is authenticated
$monitor = getPerformanceMonitor();
$metrics = $monitor->getMetrics();

// Get system performance data
function getSystemPerformance() {
    $data = [
        'memory_usage' => memory_get_usage(true),
        'memory_peak' => memory_get_peak_usage(true),
        'memory_limit' => ini_get('memory_limit'),
        'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
        'file_count' => count(get_included_files()),
        'cache_size' => getCacheSize(),
        'database_queries' => $GLOBALS['db_query_count'] ?? 0
    ];
    
    return $data;
}

function getCacheSize() {
    $cache_dir = __DIR__ . '/../cache';
    $size = 0;
    
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }
    }
    
    return $size;
}

function getPerformanceLogs() {
    $log_dir = __DIR__ . '/../logs/performance';
    $logs = [];
    
    if (is_dir($log_dir)) {
        $files = glob($log_dir . '/performance_*.json');
        rsort($files); // Get latest files first
        
        $files = array_slice($files, 0, 5); // Last 5 days of logs
        
        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach (array_reverse($lines) as $line) {
                $decoded = json_decode($line, true);
                if ($decoded) {
                    $logs[] = $decoded;
                }
                if (count($logs) >= 50) break 2;
            }
        }
    }
    
    return $logs;
}

$system_perf = getSystemPerformance();
$logs = getPerformanceLogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .metric-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .log-entry {
            border-left: 4px solid #667eea;
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        .performance-good { color: #28a745; }
        .performance-warning { color: #ffc107; }
        .performance-critical { color: #dc3545; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin/">
                <i class="fas fa-tachometer-alt me-2"></i>Performance Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin/enhanced_dashboard.php">
                    <i class="fas fa-arrow-left me-1"></i>Back to Admin
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">
            <i class="fas fa-chart-line me-2"></i>System Performance Monitor
        </h1>

        <!-- Current Performance Metrics -->
        <div class="row">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value"><?php echo $system_perf['execution_time'] * 1000; ?> ms</div>
                    <div class="metric-label">Page Load Time</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value"><?php echo round($system_perf['memory_usage'] / 1024 / 1024, 2); ?> MB</div>
                    <div class="metric-label">Memory Usage</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value"><?php echo $system_perf['file_count']; ?></div>
                    <div class="metric-label">Files Included</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value"><?php echo $system_perf['database_queries']; ?></div>
                    <div class="metric-label">Database Queries</div>
                </div>
            </div>
        </div>

        <!-- Performance Status -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-heartbeat me-2"></i>Performance Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Cache Status:</strong> 
                            <?php if (CACHE_ENABLED): ?>
                                <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Disabled</span>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Asset Optimization:</strong>
                            <?php if (ASSET_OPTIMIZATION): ?>
                                <span class="badge bg-success">Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Disabled</span>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Cache Size:</strong> <?php echo round($system_perf['cache_size'] / 1024, 2); ?> KB
                        </div>
                        <div class="mb-3">
                            <strong>Memory Limit:</strong> <?php echo $system_perf['memory_limit']; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Peak Memory:</strong> <?php echo round($system_perf['memory_peak'] / 1024 / 1024, 2); ?> MB
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-cogs me-2"></i>Optimization Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="cacheToggle" 
                                   <?php echo CACHE_ENABLED ? 'checked' : ''; ?> disabled>
                            <label class="form-check-label" for="cacheToggle">
                                Enable Caching
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="assetToggle" 
                                   <?php echo ASSET_OPTIMIZATION ? 'checked' : ''; ?> disabled>
                            <label class="form-check-label" for="assetToggle">
                                Asset Optimization
                            </label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="minifyToggle" 
                                   <?php echo MINIFY_ASSETS ? 'checked' : ''; ?> disabled>
                            <label class="form-check-label" for="minifyToggle">
                                Minify Assets
                            </label>
                        </div>
                        <hr>
                        <button class="btn btn-primary btn-sm" onclick="clearCache()">
                            <i class="fas fa-trash me-1"></i>Clear Cache
                        </button>
                        <button class="btn btn-success btn-sm" onclick="optimizeAssets()">
                            <i class="fas fa-magic me-1"></i>Optimize Assets
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Logs -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list me-2"></i>Recent Performance Logs</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($logs)): ?>
                            <p class="text-muted">No performance logs available.</p>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <div class="log-entry">
                                    <small class="text-muted"><?php echo $log['timestamp']; ?></small>
                                    <strong><?php echo $log['url']; ?></strong>
                                    <span class="float-end">
                                        Time: <?php echo $log['execution_time'] * 1000; ?> ms | 
                                        Memory: <?php echo round($log['memory_peak'] / 1024 / 1024, 2); ?> MB | 
                                        Slow Queries: <?php echo count($log['slow_queries'] ?? []); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function clearCache() {
            if (confirm('Are you sure you want to clear all cache?')) {
                // Implementation would go here
                alert('Cache cleared successfully!');
            }
        }
        
        function optimizeAssets() {
            alert('Asset optimization started!');
            // Implementation would go here
        }
    </script>
</body>
</html>

<?php
// Log this page view
$monitor->end();
?>
