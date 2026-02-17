<?php
/**
 * System Status Dashboard - APS Dream Homes
 * Real-time monitoring of all system components
 */

require_once 'includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>System Status - APS Dream Homes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .status-container { max-width: 1400px; margin: 20px auto; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .status-card { background: white; border-radius: 15px; padding: 20px; margin: 15px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .status-online { color: #10b981; }
        .status-offline { color: #ef4444; }
        .status-warning { color: #f59e0b; }
        .progress-ring { width: 60px; height: 60px; }
        .metric-value { font-size: 2rem; font-weight: bold; }
        .metric-label { color: #666; font-size: 0.9rem; }
        .system-health { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .action-btn { margin: 5px; }
        .refresh-btn { position: fixed; bottom: 20px; right: 20px; }
    </style>
</head>
<body>
    <div class='status-container'>
        <div class='text-center mb-4'>
            <h1><i class='fas fa-heartbeat me-2'></i>System Status Dashboard</h1>
            <p class='lead'>APS Dream Homes - Real-time System Monitoring</p>
            <div class='badge bg-success fs-6'>Last Updated: " . date('Y-m-d H:i:s') . "</div>
        </div>";

// Overall System Health
$health_score = 0;
$total_checks = 0;

// Database Status
$db_status = 'online';
$db_message = 'Connected and operational';
try {
    $conn->query("SELECT 1");
    $health_score += 25;
    $total_checks++;
} catch (Exception $e) {
    $db_status = 'offline';
    $db_message = 'Connection failed: ' . $e->getMessage();
    $total_checks++;
}

// Employee Tables Status
$employee_tables = ['employees', 'employee_tasks', 'employee_activities'];
$tables_status = 'online';
$tables_count = 0;
foreach ($employee_tables as $table) {
    try {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            $tables_count++;
        }
    } catch (Exception $e) {
        // Table check failed
    }
}

if ($tables_count === count($employee_tables)) {
    $health_score += 25;
    $tables_status = 'online';
} elseif ($tables_count > 0) {
    $health_score += 15;
    $tables_status = 'warning';
} else {
    $tables_status = 'offline';
}
$total_checks++;

// Admin System Status
$admin_status = 'online';
$admin_count = 0;
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM admin");
    $admin_count = $result->fetch_assoc()['count'];
    if ($admin_count > 0) {
        $health_score += 25;
        $admin_status = 'online';
    } else {
        $admin_status = 'warning';
        $health_score += 10;
    }
} catch (Exception $e) {
    $admin_status = 'offline';
}
$total_checks++;

// Employee System Status
$employee_status = 'online';
$employee_count = 0;
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'");
    $employee_count = $result->fetch_assoc()['count'];
    if ($employee_count > 0) {
        $health_score += 25;
        $employee_status = 'online';
    } else {
        $employee_status = 'warning';
        $health_score += 10;
    }
} catch (Exception $e) {
    $employee_status = 'offline';
}
$total_checks++;

$overall_health = $total_checks > 0 ? round(($health_score / ($total_checks * 25)) * 100) : 0;

echo "<div class='system-health status-card text-center p-4'>
    <h2 class='mb-3'><i class='fas fa-shield-alt me-2'></i>Overall System Health</h2>
    <div class='row align-items-center'>
        <div class='col-md-4'>
            <div class='metric-value text-white'>$overall_health%</div>
            <div class='metric-label text-white-50'>Health Score</div>
        </div>
        <div class='col-md-8'>
            <div class='row text-start'>
                <div class='col-6'>
                    <div><i class='fas fa-database me-2'></i>Database: <span class='text-white'>$db_status</span></div>
                    <div><i class='fas fa-table me-2'></i>Tables: <span class='text-white'>$tables_count/" . count($employee_tables) . "</span></div>
                </div>
                <div class='col-6'>
                    <div><i class='fas fa-user-shield me-2'></i>Admin: <span class='text-white'>$admin_status</span></div>
                    <div><i class='fas fa-users me-2'></i>Employees: <span class='text-white'>$employee_status</span></div>
                </div>
            </div>
        </div>
    </div>
</div>";

// Detailed Status Cards
echo "<div class='row'>
    <div class='col-md-6'>
        <div class='status-card'>
            <h4 class='status-$db_status'><i class='fas fa-database me-2'></i>Database Connection</h4>
            <div class='row align-items-center'>
                <div class='col-8'>
                    <div class='metric-value status-$db_status'>" . ucfirst($db_status) . "</div>
                    <div class='metric-label'>$db_message</div>
                </div>
                <div class='col-4 text-end'>
                    <i class='fas fa-database fa-3x status-$db_status'></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class='col-md-6'>
        <div class='status-card'>
            <h4 class='status-$tables_status'><i class='fas fa-table me-2'></i>Employee Tables</h4>
            <div class='row align-items-center'>
                <div class='col-8'>
                    <div class='metric-value status-$tables_status'>$tables_count/" . count($employee_tables) . "</div>
                    <div class='metric-label'>Tables created</div>
                </div>
                <div class='col-4 text-end'>
                    <i class='fas fa-table fa-3x status-$tables_status'></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class='row'>
    <div class='col-md-6'>
        <div class='status-card'>
            <h4 class='status-$admin_status'><i class='fas fa-user-shield me-2'></i>Admin System</h4>
            <div class='row align-items-center'>
                <div class='col-8'>
                    <div class='metric-value status-$admin_status'>$admin_count</div>
                    <div class='metric-label'>Admin accounts</div>
                </div>
                <div class='col-4 text-end'>
                    <i class='fas fa-user-shield fa-3x status-$admin_status'></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class='col-md-6'>
        <div class='status-card'>
            <h4 class='status-$employee_status'><i class='fas fa-users me-2'></i>Employee System</h4>
            <div class='row align-items-center'>
                <div class='col-8'>
                    <div class='metric-value status-$employee_status'>$employee_count</div>
                    <div class='metric-label'>Active employees</div>
                </div>
                <div class='col-4 text-end'>
                    <i class='fas fa-users fa-3x status-$employee_status'></i>
                </div>
            </div>
        </div>
    </div>
</div>";

// System Actions
echo "<div class='status-card'>
    <h4><i class='fas fa-tools me-2'></i>System Actions</h4>
    <div class='row'>
        <div class='col-md-3'>
            <button class='btn btn-success action-btn w-100' onclick='runSetup()'>
                <i class='fas fa-play me-2'></i>Run Setup
            </button>
        </div>
        <div class='col-md-3'>
            <button class='btn btn-info action-btn w-100' onclick='runTest()'>
                <i class='fas fa-vial me-2'></i>Run Tests
            </button>
        </div>
        <div class='col-md-3'>
            <button class='btn btn-warning action-btn w-100' onclick='createAdmin()'>
                <i class='fas fa-user-plus me-2'></i>Create Admin
            </button>
        </div>
        <div class='col-md-3'>
            <button class='btn btn-primary action-btn w-100' onclick='viewDatabase()'>
                <i class='fas fa-database me-2'></i>View Database
            </button>
        </div>
    </div>
</div>";

// Recommendations
echo "<div class='status-card'>
    <h4><i class='fas fa-lightbulb me-2'></i>System Recommendations</h4>";

if ($overall_health === 100) {
    echo "<div class='alert alert-success'>
        <i class='fas fa-check-circle me-2'></i>
        <strong>Excellent!</strong> Your system is fully operational and ready for production use.
    </div>";
} elseif ($overall_health >= 75) {
    echo "<div class='alert alert-info'>
        <i class='fas fa-info-circle me-2'></i>
        <strong>Good!</strong> System is mostly operational. Minor improvements recommended.
    </div>";
} elseif ($overall_health >= 50) {
    echo "<div class='alert alert-warning'>
        <i class='fas fa-exclamation-triangle me-2'></i>
        <strong>Attention Needed!</strong> Some components require setup for full functionality.
    </div>";
} else {
    echo "<div class='alert alert-danger'>
        <i class='fas fa-times-circle me-2'></i>
        <strong>Critical Issues!</strong> System requires immediate attention and setup.
    </div>";
}

if ($tables_count < count($employee_tables)) {
    echo "<div class='alert alert-warning mt-2'>
        <i class='fas fa-table me-2'></i>
        <strong>Missing Tables:</strong> Run the setup script to create missing employee tables.
    </div>";
}

if ($admin_count === 0) {
    echo "<div class='alert alert-warning mt-2'>
        <i class='fas fa-user-shield me-2'></i>
        <strong>No Admin:</strong> Create an admin account to manage the system.
    </div>";
}

if ($employee_count === 0) {
    echo "<div class='alert alert-info mt-2'>
        <i class='fas fa-users me-2'></i>
        <strong>No Employees:</strong> Create sample employees to test the system.
    </div>";
}

echo "</div>";

echo "<script>
function runSetup() {
    window.open('setup_employee_system.php', '_blank');
}

function runTest() {
    window.open('test_employee_system.php', '_blank');
}

function createAdmin() {
    window.open('create_first_admin.php', '_blank');
}

function viewDatabase() {
    window.open('database_explorer.php', '_blank');
}

// Auto-refresh every 30 seconds
setTimeout(() => {
    location.reload();
}, 30000);
</script>

<button class='btn btn-primary refresh-btn' onclick='location.reload()'>
    <i class='fas fa-sync me-2'></i>Refresh
</button>

<div class='text-center mt-4 mb-3'>
    <hr>
    <p class='text-muted'>
        <i class='fas fa-info-circle me-1'></i>
        System Status Dashboard - APS Dream Homes<br>
        <small>Auto-refreshes every 30 seconds</small>
    </p>
</div>

</div>
</body>
</html>";
?>
