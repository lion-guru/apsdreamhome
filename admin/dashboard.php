<?php
/**
 * Admin Dashboard
 * 
 * Comprehensive admin dashboard with system overview, statistics,
 * and quick access to all admin functions.
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Security.php';

class AdminDashboard {
    private $db;
    private $security;
    
    public function __construct() {
        $this->db = new Database();
        $this->security = new Security();
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];
        
        try {
            // User statistics
            $stats['users'] = $this->getUserStats();
            
            // Property statistics
            $stats['properties'] = $this->getPropertyStats();
            
            // System statistics
            $stats['system'] = $this->getSystemStats();
            
            // Recent activities
            $stats['recent_activities'] = $this->getRecentActivities();
            
            // Security alerts
            $stats['security_alerts'] = $this->getSecurityAlerts();
            
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get user statistics
     */
    private function getUserStats() {
        $stats = [];
        
        // Total users
        $sql = "SELECT COUNT(*) as total FROM users";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();
        
        // Active users (last 30 days)
        $sql = "SELECT COUNT(*) as active FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['active'] = $stmt->fetchColumn();
        
        // New users (last 7 days)
        $sql = "SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['new_users'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Get property statistics
     */
    private function getPropertyStats() {
        $stats = [];
        
        // Total properties
        $sql = "SELECT COUNT(*) as total FROM properties";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();
        
        // Available properties
        $sql = "SELECT COUNT(*) as available FROM properties WHERE status = 'available'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['available'] = $stmt->fetchColumn();
        
        // Sold properties
        $sql = "SELECT COUNT(*) as sold FROM properties WHERE status = 'sold'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['sold'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Get system statistics
     */
    private function getSystemStats() {
        $stats = [];
        
        // Database size
        $sql = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS db_size 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['database_size'] = $stmt->fetchColumn() . ' MB';
        
        // PHP version
        $stats['php_version'] = PHP_VERSION;
        
        // Server uptime
        $stats['server_uptime'] = $this->getServerUptime();
        
        // Memory usage
        $stats['memory_usage'] = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
        
        return $stats;
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities() {
        $activities = [];
        
        // Recent user registrations
        $sql = "SELECT 'User Registration' as activity, username, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Recent property additions
        $sql = "SELECT 'Property Added' as activity, title as username, created_at 
                FROM properties 
                ORDER BY created_at DESC 
                LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }
    
    /**
     * Get security alerts
     */
    private function getSecurityAlerts() {
        $alerts = [];
        
        // Failed login attempts
        $sql = "SELECT COUNT(*) as failed_logins 
                FROM login_attempts 
                WHERE success = 0 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $failedLogins = $stmt->fetchColumn();
        
        if ($failedLogins > 10) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "High number of failed login attempts: $failedLogins in last 24 hours",
                'icon' => 'fas fa-exclamation-triangle'
            ];
        }
        
        // Suspicious activities
        $sql = "SELECT COUNT(*) as suspicious 
                FROM security_logs 
                WHERE level = 'suspicious' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $suspicious = $stmt->fetchColumn();
        
        if ($suspicious > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "$suspicious suspicious activities detected in last 24 hours",
                'icon' => 'fas fa-shield-alt'
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Get server uptime
     */
    private function getServerUptime() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return "Load: " . round($load[0], 2);
        }
        return "N/A";
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $dashboard = new AdminDashboard();
    
    switch ($_POST['action']) {
        case 'get_stats':
            echo json_encode($dashboard->getDashboardStats());
            exit;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
}

// Initialize dashboard
$dashboard = new AdminDashboard();
$stats = $dashboard->getDashboardStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .activity-item {
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .alert-item {
            border-left: 3px solid;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .alert-warning { border-left-color: #ffc107; }
        .alert-danger { border-left-color: #dc3545; }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="unified_key_management.php">
                    <i class="fas fa-key"></i> Key Management
                </a>
                <a class="nav-link" href="user_management.php">
                    <i class="fas fa-users"></i> Users
                </a>
                <a class="nav-link" href="property_management.php">
                    <i class="fas fa-home"></i> Properties
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['users']['total'] ?? 0; ?></h4>
                                <p class="card-text">Total Users</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <small>
                            <i class="fas fa-user-plus"></i> <?php echo $stats['users']['new_users'] ?? 0; ?> new this week
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['properties']['total'] ?? 0; ?></h4>
                                <p class="card-text">Properties</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-home fa-2x"></i>
                            </div>
                        </div>
                        <small>
                            <i class="fas fa-check-circle"></i> <?php echo $stats['properties']['available'] ?? 0; ?> available
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['users']['active'] ?? 0; ?></h4>
                                <p class="card-text">Active Users</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <small>
                            <i class="fas fa-clock"></i> Last 30 days
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['properties']['sold'] ?? 0; ?></h4>
                                <p class="card-text">Sold Properties</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-handshake fa-2x"></i>
                            </div>
                        </div>
                        <small>
                            <i class="fas fa-chart-line"></i> Total sales
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info and Charts -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-server"></i> System Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td><?php echo $stats['system']['php_version'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Database Size:</strong></td>
                                <td><?php echo $stats['system']['database_size'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Memory Usage:</strong></td>
                                <td><?php echo $stats['system']['memory_usage'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Server Load:</strong></td>
                                <td><?php echo $stats['system']['server_uptime'] ?? 'N/A'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie"></i> Property Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="propertyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Alerts and Recent Activities -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-shield-alt"></i> Security Alerts</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($stats['security_alerts'])): ?>
                            <p class="text-muted">No security alerts at this time.</p>
                        <?php else: ?>
                            <?php foreach ($stats['security_alerts'] as $alert): ?>
                                <div class="alert-item alert-<?php echo $alert['type']; ?>">
                                    <i class="<?php echo $alert['icon']; ?>"></i>
                                    <?php echo $alert['message']; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($stats['recent_activities'])): ?>
                            <p class="text-muted">No recent activities.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($stats['recent_activities'], 0, 5) as $activity): ?>
                                <div class="activity-item">
                                    <strong><?php echo $activity['activity']; ?></strong><br>
                                    <small><?php echo $activity['username']; ?></small><br>
                                    <small class="text-muted"><?php echo date('M j, Y H:i', strtotime($activity['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Property Status Chart
        const ctx = document.getElementById('propertyChart').getContext('2d');
        const propertyChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Sold'],
                datasets: [{
                    data: [
                        <?php echo $stats['properties']['available'] ?? 0; ?>,
                        <?php echo $stats['properties']['sold'] ?? 0; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Auto-refresh dashboard every 30 seconds
        setInterval(function() {
            fetch('dashboard.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_stats'
            })
            .then(response => response.json())
            .then(data => {
                // Update stats cards
                location.reload();
            });
        }, 30000);
    </script>
</body>
</html>
