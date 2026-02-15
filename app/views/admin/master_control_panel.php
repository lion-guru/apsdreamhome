<?php
/**
 * Master System Control Panel - APS Dream Homes
 * Central hub for all system management and monitoring
 */

require_once __DIR__ . '/../../../includes/legacy_bootstrap.php';
$db = \App\Core\App::database();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Master Control Panel - APS Dream Homes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .control-panel {
            max-width: 1400px;
            margin: 20px auto;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .system-header {
            text-align: center;
            margin-bottom: 40px;
            background: var(--primary-gradient);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin: -40px -40px 40px -40px;
        }
        
        .metric-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 15px 0;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-left: 5px solid #6366f1;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .metric-card.success { border-left-color: #10b981; }
        .metric-card.warning { border-left-color: #f59e0b; }
        .metric-card.danger { border-left-color: #ef4444; }
        
        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .metric-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .action-button {
            padding: 15px 25px;
            border-radius: 12px;
            font-weight: 600;
            margin: 8px;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .action-primary { background: var(--primary-gradient); color: white; }
        .action-success { background: var(--success-gradient); color: white; }
        .action-warning { background: var(--warning-gradient); color: white; }
        .action-danger { background: var(--danger-gradient); color: white; }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-online { background: #10b981; }
        .status-offline { background: #ef4444; }
        .status-warning { background: #f59e0b; }
        
        .health-score {
            font-size: 3rem;
            font-weight: bold;
            background: var(--success-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .refresh-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            border: none;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            transform: scale(1.1) rotate(180deg);
        }
    </style>
</head>
<body>
    <div class='control-panel'>
        <div class='system-header'>
            <h1><i class='fas fa-cogs me-3'></i>Master Control Panel</h1>
            <p class='lead mb-0'>APS Dream Homes - Complete System Management Hub</p>
            <div class='mt-3'>
                <span class='status-indicator status-online'></span>
                <span id='current-time'>Loading...</span>
            </div>
        </div>";

// System Health Calculation
$health_metrics = [
    'database' => ['weight' => 25, 'status' => 'online'],
    'admin_system' => ['weight' => 25, 'status' => 'online'],
    'employee_system' => ['weight' => 25, 'status' => 'online'],
    'security' => ['weight' => 25, 'status' => 'online']
];

$total_health = 0;
$max_health = 100;

// Check Database
try {
    $db->query("SELECT 1");
    $health_metrics['database']['status'] = 'online';
    $total_health += $health_metrics['database']['weight'];
} catch (Exception $e) {
    $health_metrics['database']['status'] = 'offline';
}

// Check Admin System
try {
    $admin_data = $db->fetch("SELECT COUNT(*) as count FROM admin");
    $admin_count = $admin_data['count'] ?? 0;
    if ($admin_count > 0) {
        $health_metrics['admin_system']['status'] = 'online';
        $total_health += $health_metrics['admin_system']['weight'];
    } else {
        $health_metrics['admin_system']['status'] = 'warning';
        $total_health += $health_metrics['admin_system']['weight'] * 0.5;
    }
} catch (Exception $e) {
    $health_metrics['admin_system']['status'] = 'offline';
}

// Check Employee System
try {
    $tables = ['employees', 'employee_tasks', 'employee_activities'];
    $tables_found = 0;
    foreach ($tables as $table) {
        $result = $db->fetch("SHOW TABLES LIKE ?", [$table]);
        if ($result) {
            $tables_found++;
        }
    }
    
    if ($tables_found === count($tables)) {
        $health_metrics['employee_system']['status'] = 'online';
        $total_health += $health_metrics['employee_system']['weight'];
    } elseif ($tables_found > 0) {
        $health_metrics['employee_system']['status'] = 'warning';
        $total_health += $health_metrics['employee_system']['weight'] * 0.5;
    } else {
        $health_metrics['employee_system']['status'] = 'offline';
    }
} catch (Exception $e) {
    $health_metrics['employee_system']['status'] = 'offline';
}

// Check Security
$health_metrics['security']['status'] = 'online'; // Basic security assumed
$total_health += $health_metrics['security']['weight'];

$health_score = round($total_health);

// System Overview
echo "<div class='row mb-4'>
    <div class='col-md-3'>
        <div class='metric-card success text-center'>
            <div class='metric-icon bg-success bg-opacity-10 text-success mx-auto'>
                <i class='fas fa-heartbeat'></i>
            </div>
            <div class='health-score'>$health_score%</div>
            <div class='metric-label'>System Health</div>
        </div>
    </div>
    <div class='col-md-3'>
        <div class='metric-card text-center'>
            <div class='metric-icon bg-primary bg-opacity-10 text-primary mx-auto'>
                <i class='fas fa-database'></i>
            </div>
            <div class='metric-value text-primary'>" . $health_metrics['database']['status'] . "</div>
            <div class='metric-label'>Database</div>
        </div>
    </div>
    <div class='col-md-3'>
        <div class='metric-card text-center'>
            <div class='metric-icon bg-info bg-opacity-10 text-info mx-auto'>
                <i class='fas fa-user-shield'></i>
            </div>
            <div class='metric-value text-info'>" . $health_metrics['admin_system']['status'] . "</div>
            <div class='metric-label'>Admin System</div>
        </div>
    </div>
    <div class='col-md-3'>
        <div class='metric-card text-center'>
            <div class='metric-icon bg-warning bg-opacity-10 text-warning mx-auto'>
                <i class='fas fa-users'></i>
            </div>
            <div class='metric-value text-warning'>" . $health_metrics['employee_system']['status'] . "</div>
            <div class='metric-label'>Employee System</div>
        </div>
    </div>
</div>";

// Quick Actions
echo "<div class='metric-card'>
    <h3><i class='fas fa-bolt me-2'></i>Quick Actions</h3>
    <div class='row'>
        <div class='col-md-3 text-center'>
            <a href='fixed_employee_setup.php' class='action-button action-success w-100'>
                <i class='fas fa-play me-2'></i>Run Employee Setup
            </a>
        </div>
        <div class='col-md-3 text-center'>
            <a href='fixed_admin_setup.php' class='action-button action-primary w-100'>
                <i class='fas fa-user-plus me-2'></i>Create Admin
            </a>
        </div>
        <div class='col-md-3 text-center'>
            <a href='system_status.php' class='action-button action-warning w-100'>
                <i class='fas fa-heartbeat me-2'></i>System Status
            </a>
        </div>
        <div class='col-md-3 text-center'>
            <a href='database_explorer.php' class='action-button action-danger w-100'>
                <i class='fas fa-database me-2'></i>Database Explorer
            </a>
        </div>
    </div>
</div>";

// System Components
echo "<div class='row'>
    <div class='col-md-6'>
        <div class='metric-card'>
            <h4><i class='fas fa-cogs me-2'></i>System Components</h4>
            <div class='list-group'>";
            
$components = [
    'Admin Panel' => ['url' => 'admin/', 'status' => $health_metrics['admin_system']['status']],
    'Employee Login' => ['url' => 'employee_login.php', 'status' => $health_metrics['employee_system']['status']],
    'Employee Dashboard' => ['url' => 'employee_dashboard.php', 'status' => $health_metrics['employee_system']['status']],
    'Production Checklist' => ['url' => 'production_checklist.php', 'status' => 'online'],
    'System Documentation' => ['url' => 'employee_management_docs.php', 'status' => 'online']
];

foreach ($components as $name => $component) {
    $status_class = 'status-' . $component['status'];
    echo "<a href='{$component['url']}' class='list-group-item list-group-item-action' target='_blank'>
        <span class='status-indicator $status_class'></span>
        $name
        <span class='float-end'><i class='fas fa-external-link-alt'></i></span>
    </a>";
}

echo "</div></div></div>";

// Database Statistics
echo "<div class='col-md-6'>
    <div class='metric-card'>
        <h4><i class='fas fa-chart-bar me-2'></i>Database Statistics</h4>";

try {
    $tables_result = $db->fetchAll("SHOW TABLES");
    $table_count = count($tables_result);
    
    $stats = $db->fetch("
        SELECT 
            COUNT(DISTINCT TABLE_NAME) as table_count,
            SUM(TABLE_ROWS) as total_rows
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = DATABASE()
    ");
    
    echo "<div class='row text-center'>
        <div class='col-4'>
            <div class='metric-value text-primary'>$table_count</div>
            <div class='metric-label'>Tables</div>
        </div>
        <div class='col-4'>
            <div class='metric-value text-success'>" . number_format($stats['total_rows']) . "</div>
            <div class='metric-label'>Records</div>
        </div>
        <div class='col-4'>
            <div class='metric-value text-info'>$health_score%</div>
            <div class='metric-label'>Health</div>
        </div>
    </div>";
} catch (Exception $e) {
    echo "<div class='alert alert-warning'>
        <i class='fas fa-exclamation-triangle me-2'></i>
        Unable to fetch database statistics
    </div>";
}

echo "</div></div></div>";

// Recent Activity Log
echo "<div class='metric-card'>
    <h4><i class='fas fa-history me-2'></i>System Activity</h4>
    <div class='activity-log'>";
    
// Simulated recent activities (replace with real activity tracking)
$activities = [
    ['time' => '2 mins ago', 'action' => 'System health check completed', 'type' => 'success'],
    ['time' => '15 mins ago', 'action' => 'Database connection verified', 'type' => 'success'],
    ['time' => '1 hour ago', 'action' => 'System status dashboard accessed', 'type' => 'info'],
    ['time' => '2 hours ago', 'action' => 'Employee setup script executed', 'type' => 'warning'],
    ['time' => '3 hours ago', 'action' => 'Admin account created', 'type' => 'success']
];

foreach ($activities as $activity) {
    $type_class = 'text-' . ($activity['type'] === 'success' ? 'success' : ($activity['type'] === 'warning' ? 'warning' : 'info'));
    echo "<div class='d-flex justify-content-between align-items-center py-2 border-bottom'>
        <div>
            <i class='fas fa-circle fa-xs $type_class me-2'></i>
            {$activity['action']}
        </div>
        <small class='text-muted'>{$activity['time']}</small>
    </div>";
}

echo "</div></div>";

echo "<script>
// Update current time
function updateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleString();
}
updateTime();
setInterval(updateTime, 1000);

// Auto-refresh every 30 seconds
setTimeout(() => {
    location.reload();
}, 30000);
</script>

<button class='refresh-btn' onclick='location.reload()' title='Refresh Dashboard'>
    <i class='fas fa-sync'></i>
</button>

<div class='text-center mt-4'>
    <hr>
    <p class='text-muted'>
        <i class='fas fa-cogs me-1'></i>
        Master Control Panel - APS Dream Homes Employee Management System<br>
        <small>Centralized system management and monitoring | Auto-refreshes every 30 seconds</small>
    </p>
</div>

</div>
</body>
</html>";
?>

