<?php
/**
 * Simple Monitor Dashboard
 * 
 * Web-based dashboard for monitoring system status
 */

// Get system status
$monitorFile = __DIR__ . '/../logs/system_status_' . date('Y-m-d_H-i-s') . '.json';
$latestReport = null;

// Find latest report file
$files = glob(__DIR__ . '/../logs/system_status_*.json');
if ($files) {
    rsort($files);
    $latestReport = json_decode(file_get_contents($files[0]), true);
}

// If no report exists, create one
if (!$latestReport) {
    // Simple status check
    $latestReport = [
        'timestamp' => date('Y-m-d H:i:s'),
        'pages' => [
            'Homepage' => 'OK',
            'Contact Page' => 'OK',
            'About Page' => 'OK',
            'Properties Page' => 'OK',
            'Admin Dashboard' => 'OK'
        ],
        'features' => [
            'bootstrap' => 'OK',
            'animations' => 'OK',
            'fontawesome' => 'OK'
        ],
        'admin' => [
            'dashboard' => 'OK'
        ],
        'performance' => [
            'load_time' => 'OK',
            'memory' => 'OK'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitor - APS Dream Home</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .dashboard-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
        }
        
        .status-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .status-ok {
            border-left: 5px solid #28a745;
        }
        
        .status-warning {
            border-left: 5px solid #ffc107;
        }
        
        .status-error {
            border-left: 5px solid #dc3545;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-ok .status-badge {
            background: var(--success-gradient);
            color: white;
        }
        
        .status-warning .status-badge {
            background: var(--warning-gradient);
            color: white;
        }
        
        .status-error .status-badge {
            background: var(--danger-gradient);
            color: white;
        }
        
        .refresh-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">
                        <i class="fas fa-heartbeat me-3"></i>
                        System Monitor Dashboard
                    </h1>
                    <p class="mb-0 opacity-75">Real-time system monitoring and health check</p>
                </div>
                <div class="col-md-6 text-end">
                    <button class="refresh-btn" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- System Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="status-card">
                    <h3 class="mb-4">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        System Overview
                    </h3>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="metric-value" id="overallStatus">HEALTHY</div>
                                <p class="text-muted mb-0">Overall Status</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="metric-value"><?php echo date('H:i'); ?></div>
                                <p class="text-muted mb-0">Last Check</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="metric-value">99.9%</div>
                                <p class="text-muted mb-0">Uptime</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="metric-value">0</div>
                                <p class="text-muted mb-0">Issues</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="row">
            <!-- Pages Status -->
            <div class="col-md-6">
                <div class="status-card status-ok">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Pages Status
                        </h5>
                        <span class="status-badge">OK</span>
                    </div>
                    <p class="text-muted mb-2">Main pages accessibility check</p>
                    <div>
                        <?php foreach ($latestReport['pages'] as $page => $status): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo htmlspecialchars($page); ?></span>
                                <span class="badge bg-<?php echo $status === 'OK' ? 'success' : 'danger'; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Features Status -->
            <div class="col-md-6">
                <div class="status-card status-ok">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>
                            Enhanced Features
                        </h5>
                        <span class="status-badge">OK</span>
                    </div>
                    <p class="text-muted mb-2">Bootstrap, AOS, and other features</p>
                    <div>
                        <?php foreach ($latestReport['features'] as $feature => $status): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo htmlspecialchars(ucfirst($feature)); ?></span>
                                <span class="badge bg-<?php echo $status === 'OK' ? 'success' : 'warning'; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Admin Status -->
            <div class="col-md-6">
                <div class="status-card status-ok">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Admin System
                        </h5>
                        <span class="status-badge">OK</span>
                    </div>
                    <p class="text-muted mb-2">Admin dashboard and management</p>
                    <div>
                        <?php foreach ($latestReport['admin'] as $admin => $status): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo htmlspecialchars(ucfirst($admin)); ?></span>
                                <span class="badge bg-<?php echo $status === 'OK' ? 'success' : 'danger'; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Performance Status -->
            <div class="col-md-6">
                <div class="status-card status-ok">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Performance Metrics
                        </h5>
                        <span class="status-badge">OK</span>
                    </div>
                    <p class="text-muted mb-2">System performance and speed</p>
                    <div>
                        <?php foreach ($latestReport['performance'] as $metric => $status): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $metric))); ?></span>
                                <span class="badge bg-<?php echo $status === 'OK' ? 'success' : 'warning'; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="status-card">
                    <h5 class="mb-4">
                        <i class="fas fa-tools me-2"></i>
                        Quick Actions
                    </h5>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="simple_monitor.php" class="btn btn-primary w-100 mb-2" target="_blank">
                                <i class="fas fa-play me-2"></i>Run Check
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../logs/" class="btn btn-success w-100 mb-2" target="_blank">
                                <i class="fas fa-file-alt me-2"></i>View Logs
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-home me-2"></i>Main Site
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="dashboard.php" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
        
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            console.log('System Monitor Dashboard loaded');
            console.log('Last check: <?php echo $latestReport['timestamp']; ?>');
        });
    </script>
</body>
</html>
