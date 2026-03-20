<?php
/**
 * CM Dashboard View
 * Chief Manager Dashboard Interface
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/login');
    exit;
}

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'CM';
$admin_role = $_SESSION['admin_role'] ?? 'cm';

// Content for admin layout
ob_start();
?>

<!-- CM Dashboard Header -->
<div class="cm-dashboard-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-0">
                    <i class="fas fa-user-tie me-2"></i>
                    Chief Manager Dashboard
                </h2>
                <p class="text-white-50 mb-0">Team Management & Performance Overview</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <div class="performance-indicator">
                        <div class="indicator-label">Performance Score</div>
                        <div class="indicator-value"><?= $stats['performance_score'] ?>%</div>
                    </div>
                    <button class="btn btn-outline-light" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="glass-card stat-card bg-gradient-blue">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number"><?= number_format($stats['team_size']) ?></div>
            <div class="stat-label">Team Members</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +12% this month
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card stat-card bg-gradient-green">
            <div class="stat-icon">
                <i class="fas fa-project-diagram"></i>
            </div>
            <div class="stat-number"><?= number_format($stats['active_projects']) ?></div>
            <div class="stat-label">Active Projects</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +8% this month
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card stat-card bg-gradient-orange">
            <div class="stat-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-number"><?= number_format($stats['monthly_sales']) ?></div>
            <div class="stat-label">Monthly Sales</div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i> -3% this month
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card stat-card bg-gradient-purple">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-number"><?= $stats['performance_score'] ?>%</div>
            <div class="stat-label">Performance</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +5.2% this month
            </div>
        </div>
    </div>
</div>

<!-- Main Dashboard Content -->
<div class="row">
    <!-- Team Performance -->
    <div class="col-md-8">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    Team Performance
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="viewFullTeam()">
                    View All
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Team Member</th>
                            <th>Role</th>
                            <th>Properties</th>
                            <th>Sales</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($teamPerformance)): ?>
                            <?php foreach ($teamPerformance as $member): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="member-avatar me-2">
                                                <?= strtoupper(substr($member['name'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <div class="member-name"><?= htmlspecialchars($member['name']) ?></div>
                                                <small class="text-white-50"><?= htmlspecialchars($member['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= ucfirst($member['role']) ?></span>
                                    </td>
                                    <td><?= number_format($member['properties_managed']) ?></td>
                                    <td><?= number_format($member['sales_count']) ?></td>
                                    <td>
                                        <div class="progress-custom">
                                            <div class="progress-bar-custom" style="width: <?= min($member['sales_count'] * 20, 100) ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-white-50">
                                    No team performance data available
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions & Activities -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="glass-card mb-4">
            <h5 class="mb-3">
                <i class="fas fa-bolt me-2"></i>
                Quick Actions
            </h5>
            <div class="quick-actions-grid">
                <div class="quick-action-card" onclick="manageTeam()">
                    <div class="quick-action-icon bg-gradient-blue">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h6>Manage Team</h6>
                    <small>Team members & roles</small>
                </div>
                <div class="quick-action-card" onclick="viewReports()">
                    <div class="quick-action-icon bg-gradient-green">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h6>Reports</h6>
                    <small>Performance analytics</small>
                </div>
                <div class="quick-action-card" onclick="assignProjects()">
                    <div class="quick-action-icon bg-gradient-orange">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h6>Assign Projects</h6>
                    <small>Distribute work</small>
                </div>
                <div class="quick-action-card" onclick="scheduleMeeting()">
                    <div class="quick-action-icon bg-gradient-purple">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h6>Schedule Meeting</h6>
                    <small>Team coordination</small>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="glass-card">
            <h5 class="mb-3">
                <i class="fas fa-history me-2"></i>
                Recent Activities
            </h5>
            <div class="activities-list">
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-dot <?= $this->getActivityClass($activity['activity_type']) ?>"></div>
                            <div class="activity-content">
                                <div class="activity-text"><?= htmlspecialchars($activity['description']) ?></div>
                                <small class="text-white-50"><?= date('M j, Y H:i', strtotime($activity['created_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-white-50 py-3">
                        No recent activities
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Projects Overview -->
<div class="row mt-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="fas fa-project-diagram me-2"></i>
                    Projects Overview
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="viewAllProjects()">
                    View All Projects
                </button>
            </div>
            
            <div class="row">
                <?php if (!empty($projectsOverview)): ?>
                    <?php foreach ($projectsOverview as $project): ?>
                        <div class="col-md-3 mb-3">
                            <div class="project-stat">
                                <div class="project-status <?= $this->getProjectStatusClass($project['status']) ?>">
                                    <?= ucfirst($project['status']) ?>
                                </div>
                                <div class="project-count"><?= number_format($project['count']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-white-50">
                        No project data available
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.cm-dashboard-header {
    background: var(--primary-gradient);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    color: white;
}

.performance-indicator {
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    padding: 1rem;
    border-radius: 12px;
    min-width: 120px;
}

.indicator-label {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 0.25rem;
}

.indicator-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
}

.member-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    font-weight: bold;
}

.member-name {
    font-weight: 600;
    color: white;
}

.activities-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-content {
    flex: 1;
    margin-left: 0.75rem;
}

.activity-text {
    color: white;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.project-stat {
    text-align: center;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.project-status {
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
}

.project-status.active {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.project-status.pending {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.project-status.completed {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.project-count {
    font-size: 2rem;
    font-weight: bold;
    color: white;
}
</style>

<script>
// Quick action functions
function refreshDashboard() {
    location.reload();
}

function manageTeam() {
    window.location.href = '<?= BASE_URL ?>admin/team';
}

function viewReports() {
    window.location.href = '<?= BASE_URL ?>admin/reports';
}

function assignProjects() {
    window.location.href = '<?= BASE_URL ?>admin/properties/assign';
}

function scheduleMeeting() {
    alert('Meeting scheduler coming soon!');
}

function viewFullTeam() {
    window.location.href = '<?= BASE_URL ?>admin/team';
}

function viewAllProjects() {
    window.location.href = '<?= BASE_URL ?>admin/properties';
}

// Auto-refresh dashboard every 30 seconds
setInterval(function() {
    refreshDashboard();
}, 30000);
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/admin_header.php';
echo $content;
require_once __DIR__ . '/../layouts/admin_footer.php';
?>