<?php
/**
 * Professional MLM Team Analytics
 * Advanced team performance and analytics dashboard
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check admin authentication
if (!isLoggedIn() || !isAdmin()) {
    redirectTo('login.php');
}

$page_title = "Professional Team Analytics";
include 'includes/header.php';

// Get analytics data
$analytics = [
    'total_teams' => $conn->query("SELECT COUNT(DISTINCT team_id) FROM mlm_tree")->fetch_row()[0] ?? 0,
    'active_teams' => $conn->query("SELECT COUNT(DISTINCT team_id) FROM mlm_tree WHERE status = 'active'")->fetch_row()[0] ?? 0,
    'team_performance' => [],
    'rank_distribution' => [],
    'top_teams' => [],
    'growth_metrics' => []
];

// Get team performance data
$team_performance = $conn->query("
    SELECT 
        t.team_id,
        t.team_name,
        COUNT(a.id) as total_members,
        SUM(mp.total_sales) as team_sales,
        SUM(mp.total_commission) as team_commissions,
        AVG(mp.target_achieved) as avg_target_achievement,
        MAX(mp.total_sales) as top_performer_sales
    FROM mlm_teams t
    JOIN mlm_tree mt ON t.team_id = mt.team_id
    JOIN associates a ON mt.associate_id = a.id
    LEFT JOIN mlm_performance mp ON a.id = mp.associate_id
    WHERE mp.month_year = '" . date('Y-m') . "'
    GROUP BY t.team_id, t.team_name
    ORDER BY team_sales DESC
    LIMIT 20
");

// Get rank distribution
$rank_distribution = $conn->query("
    SELECT 
        ml.level_name as rank_name,
        COUNT(a.id) as member_count,
        AVG(mp.total_sales) as avg_sales,
        AVG(mp.total_commission) as avg_commission,
        AVG(mp.target_achieved) as avg_target_achievement
    FROM mlm_levels ml
    LEFT JOIN associates a ON ml.level_name = a.rank
    LEFT JOIN mlm_performance mp ON a.id = mp.associate_id AND mp.month_year = '" . date('Y-m') . "'
    GROUP BY ml.level_name, ml.level_order
    ORDER BY ml.level_order
");

// Get growth metrics
$growth_metrics = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as new_associates,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_associates,
        AVG(total_sales) as avg_sales,
        AVG(total_commission) as avg_commission
    FROM associates a
    LEFT JOIN mlm_performance mp ON a.id = mp.associate_id
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");

// Get top performing teams
$top_teams = $conn->query("
    SELECT 
        t.team_id,
        t.team_name,
        t.team_leader_id,
        a.name as leader_name,
        COUNT(mt.associate_id) as team_size,
        SUM(mp.total_sales) as total_sales,
        SUM(mp.total_commission) as total_commissions,
        AVG(mp.target_achieved) as avg_target_achievement,
        (SUM(mp.total_sales) / COUNT(mt.associate_id)) as sales_per_member
    FROM mlm_teams t
    JOIN associates a ON t.team_leader_id = a.id
    JOIN mlm_tree mt ON t.team_id = mt.team_id
    LEFT JOIN mlm_performance mp ON mt.associate_id = mp.associate_id AND mp.month_year = '" . date('Y-m') . "'
    GROUP BY t.team_id, t.team_name, t.team_leader_id, a.name
    HAVING team_size > 0
    ORDER BY total_sales DESC
    LIMIT 10
");

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 text-primary mb-0">
                <i class="fas fa-chart-pie me-2"></i>Professional Team Analytics
            </h1>
            <p class="text-muted">Advanced team performance and analytics dashboard</p>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($analytics['total_teams']); ?></h4>
                            <p class="mb-0">Total Teams</p>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($analytics['active_teams']); ?></h4>
                            <p class="mb-0">Active Teams</p>
                        </div>
                        <i class="fas fa-user-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($top_teams->num_rows); ?></h4>
                            <p class="mb-0">Top Performing Teams</p>
                        </div>
                        <i class="fas fa-trophy fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($rank_distribution->num_rows); ?></h4>
                            <p class="mb-0">Rank Levels</p>
                        </div>
                        <i class="fas fa-layer-group fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Team Performance Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="teamPerformanceChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Rank Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="rankDistributionChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Teams -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Performing Teams</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Team Name</th>
                                    <th>Team Leader</th>
                                    <th>Team Size</th>
                                    <th>Total Sales</th>
                                    <th>Total Commissions</th>
                                    <th>Avg Target Achievement</th>
                                    <th>Sales per Member</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                while($team = $top_teams->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?php echo $rank <= 3 ? 'warning' : 'secondary'; ?>">
                                            #<?php echo $rank++; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($team['team_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($team['leader_name']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo number_format($team['team_size']); ?></span>
                                    </td>
                                    <td>₹<?php echo number_format($team['total_sales']); ?></td>
                                    <td>₹<?php echo number_format($team['total_commissions']); ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-<?php echo $team['avg_target_achievement'] >= 100 ? 'success' : 'warning'; ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo min($team['avg_target_achievement'], 100); ?>%">
                                                <?php echo round($team['avg_target_achievement']); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>₹<?php echo number_format($team['sales_per_member']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Team Analytics">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rank Performance Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Rank Performance Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Member Count</th>
                                    <th>Average Sales</th>
                                    <th>Average Commission</th>
                                    <th>Average Target Achievement</th>
                                    <th>Performance Score</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($rank = $rank_distribution->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($rank['rank_name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo number_format($rank['member_count']); ?></span>
                                    </td>
                                    <td>₹<?php echo number_format($rank['avg_sales']); ?></td>
                                    <td>₹<?php echo number_format($rank['avg_commission']); ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-<?php echo $rank['avg_target_achievement'] >= 100 ? 'success' : 'warning'; ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo min($rank['avg_target_achievement'], 100); ?>%">
                                                <?php echo round($rank['avg_target_achievement']); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $performance_score = ($rank['avg_target_achievement'] + 
                                                           ($rank['avg_sales'] / 100000) * 10 + 
                                                           ($rank['avg_commission'] / 10000) * 10) / 3;
                                        ?>
                                        <span class="badge bg-<?php echo $performance_score >= 80 ? 'success' : ($performance_score >= 60 ? 'warning' : 'danger'); ?>">
                                            <?php echo round($performance_score); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-arrow-<?php echo $performance_score >= 70 ? 'up text-success' : 'down text-danger'; ?>"></i>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth Analytics -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Monthly Growth Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="growthChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Team Size Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="teamSizeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Team Performance Chart
const teamCtx = document.getElementById('teamPerformanceChart').getContext('2d');
new Chart(teamCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Team Sales',
            data: [5000000, 7500000, 10000000, 12500000, 15000000, 18000000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Team Commissions',
            data: [500000, 750000, 1000000, 1250000, 1500000, 1800000],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Team Performance Over Time'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Rank Distribution Chart
const rankCtx = document.getElementById('rankDistributionChart').getContext('2d');
new Chart(rankCtx, {
    type: 'doughnut',
    data: {
        labels: ['Associate', 'Senior Associate', 'Team Leader', 'Manager', 'Senior Manager', 'Director'],
        datasets: [{
            data: [300, 150, 80, 40, 20, 10],
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Member Distribution by Rank'
            }
        }
    }
});

// Growth Chart
const growthCtx = document.getElementById('growthChart').getContext('2d');
new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'New Associates',
            data: [50, 75, 100, 125, 150, 180],
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1
        }, {
            label: 'Active Associates',
            data: [40, 60, 85, 110, 135, 165],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Monthly Growth Trends'
            }
        }
    }
});

// Team Size Chart
const teamSizeCtx = document.getElementById('teamSizeChart').getContext('2d');
new Chart(teamSizeCtx, {
    type: 'bar',
    data: {
        labels: ['1-5', '6-10', '11-20', '21-50', '51-100', '100+'],
        datasets: [{
            label: 'Number of Teams',
            data: [25, 35, 20, 12, 5, 3],
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Team Size Distribution'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>