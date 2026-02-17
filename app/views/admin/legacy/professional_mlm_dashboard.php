<?php
require_once __DIR__ . '/core/init.php';

use App\Core\Database;
$db = \App\Core\App::database();

// Enhanced session security and timeout check
if (!isAuthenticated() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

// Get admin user ID for MLM analytics
$admin_id = getAuthUserId() ?? 0;

// MLM Dashboard Analytics Class
class ProfessionalMLMDashboard {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get network analytics
    public function getNetworkAnalytics($userId = null) {
        $analytics = [];

        // Total network members
        $row = $this->db->fetch("SELECT COUNT(*) as total_members FROM associates WHERE status = 'active'");
        $analytics['total_members'] = $row['total_members'] ?? 0;

        // Active members this month
        $row = $this->db->fetch("SELECT COUNT(*) as active_this_month FROM associates WHERE status = 'active' AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $analytics['active_this_month'] = $row['active_this_month'] ?? 0;

        // Network growth trend
        $analytics['growth_trend'] = $this->db->fetchAll("SELECT DATE(created_at) as date, COUNT(*) as count FROM associates WHERE status = 'active' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) GROUP BY DATE(created_at) ORDER BY date");

        return $analytics;
    }

    // Get commission analytics
    public function getCommissionAnalytics() {
        $analytics = [];

        // Total commissions paid
        $row = $this->db->fetch("SELECT SUM(amount) as total_commissions FROM mlm_commissions WHERE status = 'paid'");
        $analytics['total_commissions'] = $row['total_commissions'] ?? 0;

        // Pending commissions
        $row = $this->db->fetch("SELECT SUM(amount) as pending_commissions FROM mlm_commissions WHERE status = 'pending'");
        $analytics['pending_commissions'] = $row['pending_commissions'] ?? 0;

        // Commission distribution by level
        $analytics['level_distribution'] = $this->db->fetchAll("SELECT level, SUM(amount) as total FROM mlm_commissions WHERE status = 'paid' GROUP BY level ORDER BY level");

        return $analytics;
    }

    // Get top performers
    public function getTopPerformers($limit = 10) {
        return $this->db->fetchAll("
            SELECT
                a.id,
                u.uname as name,
                u.uemail as email,
                a.team_size as referrals,
                a.total_business as total_commissions
            FROM associates a
            JOIN user u ON a.user_id = u.uid
            WHERE a.status = 'active'
            ORDER BY a.total_business DESC
            LIMIT ?
        ", [$limit]);
    }

    // Get recent activities
    public function getRecentActivities($limit = 20) {
        return $this->db->fetchAll("
            SELECT
                'new_member' as type,
                u.uname as user_name,
                a.created_at as activity_date,
                CONCAT('New associate joined: ', u.uname) as description
            FROM associates a
            JOIN user u ON a.user_id = u.uid
            WHERE a.status = 'active' AND a.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)

            UNION ALL

            SELECT
                'commission_paid' as type,
                u.uname as user_name,
                c.created_at as activity_date,
                CONCAT('Commission paid to: ', u.uname, ' - ₹', c.commission_amount) as description
            FROM mlm_commissions c
            JOIN associates a ON c.associate_id = a.id
            JOIN user u ON a.user_id = u.uid
            WHERE c.status = 'paid' AND c.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)

            ORDER BY activity_date DESC
            LIMIT ?
        ", [$limit]);
    }
}

// Initialize MLM Dashboard
$mlm_dashboard = new ProfessionalMLMDashboard($db);

// Get all analytics data
$network_analytics = $mlm_dashboard->getNetworkAnalytics();
$commission_analytics = $mlm_dashboard->getCommissionAnalytics();
$top_performers = $mlm_dashboard->getTopPerformers();
$recent_activities = $mlm_dashboard->getRecentActivities();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional MLM Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
    <style>
        .dashboard-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .metric-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        .activity-item {
            padding: 15px;
            border-left: 3px solid #3498db;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .activity-item.member { border-left-color: #27ae60; }
        .activity-item.commission { border-left-color: #e74c3c; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-6 text-center mb-0">Professional MLM Dashboard</h1>
                <p class="text-muted text-center">Network Performance & Analytics</p>
            </div>
        </div>

        <!-- Key Metrics Row -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card bg-primary text-white">
                    <div class="card-body text-center">
                        <div class="metric-value"><?= number_format($network_analytics['total_members']) ?></div>
                        <div class="metric-label">Total Members</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card bg-success text-white">
                    <div class="card-body text-center">
                        <div class="metric-value"><?= number_format($network_analytics['active_this_month']) ?></div>
                        <div class="metric-label">New This Month</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card bg-warning text-white">
                    <div class="card-body text-center">
                        <div class="metric-value">₹<?= number_format($commission_analytics['total_commissions'], 2) ?></div>
                        <div class="metric-label">Total Commissions</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-card bg-info text-white">
                    <div class="card-body text-center">
                        <div class="metric-value">₹<?= number_format($commission_analytics['pending_commissions'], 2) ?></div>
                        <div class="metric-label">Pending Commissions</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-8 mb-3">
                <div class="card dashboard-card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Network Growth Trend</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card dashboard-card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Commission by Level</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="commissionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performers & Recent Activities -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card dashboard-card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Top Performers</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Referrals</th>
                                        <th>Commissions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_performers as $performer): ?>
                                    <tr>
                                        <td><?= h($performer['name']) ?></td>
                                        <td><?= $performer['referrals'] ?></td>
                                        <td>₹<?= number_format($performer['total_commissions'] ?? 0, 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card dashboard-card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-list">
                            <?php foreach ($recent_activities as $activity): ?>
                            <div class="activity-item <?= $activity['type'] ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= h($activity['user_name']) ?></strong>
                                        <p class="mb-0 text-muted"><?= h($activity['description']) ?></p>
                                    </div>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($activity['activity_date'])) ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <script>
        // Network Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        const growthData = <?= json_encode($network_analytics['growth_trend']) ?>;

        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: growthData.map(item => new Date(item.date).toLocaleDateString()),
                datasets: [{
                    label: 'New Members',
                    data: growthData.map(item => item.count),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Commission Distribution Chart
        const commissionCtx = document.getElementById('commissionChart').getContext('2d');
        const commissionData = <?= json_encode($commission_analytics['level_distribution']) ?>;

        new Chart(commissionCtx, {
            type: 'doughnut',
            data: {
                labels: commissionData.map(item => 'Level ' + item.level),
                datasets: [{
                    data: commissionData.map(item => item.total),
                    backgroundColor: [
                        '#e74c3c',
                        '#f39c12',
                        '#f1c40f',
                        '#27ae60',
                        '#3498db'
                    ]
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
    </script>
</body>
</html>
