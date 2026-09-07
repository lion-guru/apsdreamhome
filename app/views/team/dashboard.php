<?php
/**
 * Team Dashboard View
 * APS Dream Home - Team Management Center
 */

$page_title = 'Team Management Center';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

// Ensure required data is available
$team = $team ?? ['name' => 'Team', 'members' => 0];
$teamInfo = $teamInfo ?? [
    'total_members' => 0,
    'active_members' => 0,
    'performance' => 0,
    'total_earnings' => 0,
    'contributing_members' => 0,
    'network_levels' => 0,
    'direct_reports' => 0
];
$performanceData = $performanceData ?? [
    'monthly_earnings' => [],
    'top_performers' => []
];
$recentActivities = $recentActivities ?? [];
$teamIncentives = $teamIncentives ?? [];
$hierarchyData = $hierarchyData ?? ['root' => null, 'levels' => []];

// Pass data to JavaScript
$jsHierarchyData = json_encode($hierarchyData);
$jsEarningsData = json_encode($performanceData['monthly_earnings'] ?? []);
?>

<?php $this->layout('layouts/base', ['title' => $page_title, 'body_class' => 'team-dashboard']); ?>

<?php $this->start('styles'); ?>
<link rel="stylesheet" href="<?= $base ?>/public/assets/css/team-dashboard.css?v=1">
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
<script>
    // Pass PHP data to JavaScript
    window.TEAM_HIERARCHY_DATA = <?= $jsHierarchyData ?>;
    window.TEAM_EARNINGS_DATA = <?= $jsEarningsData ?>;
</script>
<script src="<?= $base ?>/public/assets/js/team-dashboard.js?v=1" defer></script>
<?php $this->end(); ?>

<?php $this->start('content'); ?>
<!-- Header -->
<header class="team-header">
    <div class="header-content">
        <div class="welcome-section">
            <h1>Team Management Center <i class="bi bi-people-fill"></i></h1>
            <p>Monitor, manage, and grow your team</p>
        </div>
        <div class="team-stats">
            <div class="stat-badge">
                <i class="bi bi-trophy me-1"></i><?= htmlspecialchars($team['name'] ?? 'Team'); ?>
            </div>
            <div class="stat-badge">
                <i class="bi bi-people me-1"></i><?= htmlspecialchars($team['members'] ?? '0'); ?> members
            </div>
        </div>
    </div>
</header>

<!-- Dashboard Content -->
<main class="dashboard-content">
    <!-- Team Overview -->
    <div class="overview-grid">
        <!-- Total Members -->
        <div class="overview-card members">
            <div class="overview-icon success">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="overview-value"><?= htmlspecialchars(number_format($teamInfo['total_members'])); ?></div>
            <div class="overview-label">Total Team Members</div>
            <div class="overview-change positive">
                <i class="bi bi-person-check"></i> <?= htmlspecialchars($teamInfo['active_members'] ?? '0'); ?> active
            </div>
        </div>

        <!-- Team Performance -->
        <div class="overview-card performance">
            <div class="overview-icon warning">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="overview-value"><?= htmlspecialchars($teamInfo['performance'] ?? '0'); ?>%</div>
            <div class="overview-label">Avg Performance</div>
            <div class="overview-change positive">
                <i class="bi bi-arrow-up"></i> Team efficiency rating
            </div>
        </div>

        <!-- Team Earnings -->
        <div class="overview-card earnings">
            <div class="overview-icon info">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="overview-value">₹<?= htmlspecialchars(number_format($teamInfo['total_earnings'], 0)); ?></div>
            <div class="overview-label">Team Earnings</div>
            <div class="overview-change positive">
                <i class="bi bi-plus-circle"></i> From <?= htmlspecialchars($teamInfo['contributing_members'] ?? '0'); ?> members
            </div>
        </div>

        <!-- Team Levels -->
        <div class="overview-card">
            <div class="overview-icon primary">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div class="overview-value"><?= htmlspecialchars($teamInfo['network_levels'] ?? '0'); ?></div>
            <div class="overview-label">Network Levels</div>
            <div class="overview-change positive">
                <i class="bi bi-chevron-double-up"></i> <?= htmlspecialchars($teamInfo['direct_reports'] ?? '0'); ?> direct reports
            </div>
        </div>
    </div>

    <!-- Team Hierarchy Visualization -->
    <div class="hierarchy-section">
        <h3 class="chart-title mb-4"><i class="bi bi-diagram-3 me-2 text-primary"></i>Team Hierarchy</h3>
        <div class="hierarchy-container">
            <div id="hierarchy-chart"></div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Team Earnings Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Team Earnings Trend</h3>
                <span class="badge bg-success">Last 6 Months</span>
            </div>
            <canvas id="teamEarningsChart" width="400" height="200"></canvas>
        </div>

        <!-- Top Performers -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Top Performers</h3>
                <span class="badge bg-primary"><?= htmlspecialchars(count($performanceData['top_performers'] ?? [])); ?> members</span>
            </div>
            <div class="mt-3">
                <?php if (!empty($performanceData['top_performers'])): ?>
                    <?php foreach (array_slice($performanceData['top_performers'], 0, 5) as $performer): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="fw-bold small"><?= htmlspecialchars($performer['name'] ?? 'Unknown'); ?></div>
                            <div class="text-muted small">Level <?= htmlspecialchars($performer['level'] ?? '0'); ?></div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">₹<?= htmlspecialchars(number_format($performer['earnings'], 0)); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="bi bi-person-x text-muted"></i>
                        <p class="text-muted mt-2 small">No performance data yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="actions-section">
        <h3 class="chart-title mb-4"><i class="bi bi-lightning me-2 text-warning"></i>Team Management Actions</h3>
        <div class="action-buttons">
            <a href="<?= htmlspecialchars(route('team.members')); ?>" class="action-btn primary">
                <div class="action-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="action-content">
                    <h5>View All Members</h5>
                    <p>Manage team members</p>
                </div>
            </a>

            <a href="<?= htmlspecialchars(route('team.performance')); ?>" class="action-btn success">
                <div class="action-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="action-content">
                    <h5>Performance Analytics</h5>
                    <p>Detailed performance reports</p>
                </div>
            </a>

            <a href="<?= htmlspecialchars(route('team.communication')); ?>" class="action-btn info">
                <div class="action-icon">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <div class="action-content">
                    <h5>Team Communication</h5>
                    <p>Send messages to team</p>
                </div>
            </a>

            <a href="<?= htmlspecialchars(route('team.export')); ?>" class="action-btn primary">
                <div class="action-icon">
                    <i class="bi bi-download"></i>
                </div>
                <div class="action-content">
                    <h5>Export Team Data</h5>
                    <p>Download team reports</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Team Activities -->
    <div class="activities-section">
        <h3 class="chart-title mb-4"><i class="bi bi-activity me-2 text-info"></i>Recent Team Activities</h3>
        <?php if (!empty($recentActivities)): ?>
            <?php foreach ($recentActivities as $activity): ?>
            <div class="activity-item">
                <div class="activity-icon <?= htmlspecialchars($activity['icon_class'] ?? 'primary'); ?>">
                    <i class="bi bi-<?= htmlspecialchars($activity['icon'] ?? 'person'); ?>"></i>
                </div>
                <div class="activity-content">
                    <h4><?= htmlspecialchars($activity['title'] ?? 'Activity'); ?></h4>
                    <p><?= htmlspecialchars($activity['description'] ?? 'No description'); ?></p>
                    <small class="activity-time">
                        <?php 
                            $date = $activity['date'] ?? date('Y-m-d H:i:s');
                            try {
                                echo \Carbon\Carbon::parse($date)->diffForHumans();
                            } catch (Exception $e) {
                                echo htmlspecialchars($date);
                            }
                        ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-activity text-muted"></i>
                <p class="text-muted mt-2">No recent team activities</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Team Incentives -->
    <?php if (!empty($teamIncentives)): ?>
    <div class="incentives-section">
        <h3 class="chart-title mb-4"><i class="bi bi-trophy me-2 text-warning"></i>Team Incentives & Rewards</h3>
        <?php foreach ($teamIncentives as $incentive): ?>
        <div class="incentive-item <?= htmlspecialchars($incentive['type'] ?? 'primary'); ?>">
            <div class="incentive-header">
                <div class="incentive-title"><?= htmlspecialchars($incentive['title'] ?? 'Incentive'); ?></div>
                <div class="incentive-amount">₹<?= htmlspecialchars(number_format($incentive['amount'], 0)); ?></div>
            </div>
            <div class="incentive-description"><?= htmlspecialchars($incentive['description'] ?? 'No description'); ?></div>
            <?php if (isset($incentive['progress'])): ?>
            <div class="incentive-progress">
                <div class="progress-bar incentive-progress-bar" data-progress="<?= htmlspecialchars($incentive['progress'] ?? '0'); ?>"></div>
            </div>
            <div class="mt-1 small text-muted"><?= htmlspecialchars(round($incentive['progress'] ?? 0, 1)); ?>% complete</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>
<?php $this->end(); ?>