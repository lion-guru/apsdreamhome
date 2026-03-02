<?php
/**
 * Employee Reporting Structure View
 * Shows employee reporting hierarchy and team structure
 */
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-sitemap me-2"></i>Reporting Structure</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="printStructure()">
                <i class="fas fa-print me-2"></i>Print Structure
            </button>
            <button class="btn btn-outline-secondary" onclick="exportStructure()">
                <i class="fas fa-download me-2"></i>Export
            </button>
        </div>
    </div>

    <!-- Current Position -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user me-2"></i>My Position</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="position-info">
                                <h4 class="text-primary mb-2">
                                    <?= htmlspecialchars($reporting_structure['current_employee']['name'] ?? 'N/A') ?>
                                </h4>
                                <p class="mb-1">
                                    <strong>Position:</strong>
                                    <?= htmlspecialchars($reporting_structure['current_employee']['position'] ?? 'N/A') ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Department:</strong>
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars($reporting_structure['current_employee']['department'] ?? 'N/A') ?>
                                    </span>
                                </p>
                                <p class="mb-1">
                                    <strong>Employee ID:</strong>
                                    <?= htmlspecialchars($reporting_structure['current_employee']['employee_id'] ?? 'N/A') ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Joining Date:</strong>
                                    <?= htmlspecialchars($reporting_structure['current_employee']['joining_date'] ?? 'N/A') ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="hierarchy-preview">
                                <h6>Reporting Hierarchy</h6>
                                <?php if (!empty($reporting_structure['manager'])): ?>
                                    <div class="manager-info">
                                        <div class="d-flex align-items-center">
                                            <div class="manager-avatar me-3">
                                                <i class="fas fa-user-tie fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <strong>Reports to:</strong><br>
                                                <?= htmlspecialchars($reporting_structure['manager']['name']) ?><br>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($reporting_structure['manager']['position']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($reporting_structure['subordinates'])): ?>
                                    <div class="subordinates-info mt-3">
                                        <div class="d-flex align-items-center">
                                            <div class="subordinates-avatar me-3">
                                                <i class="fas fa-users fa-2x text-success"></i>
                                            </div>
                                            <div>
                                                <strong>Team Size:</strong><br>
                                                <?= count($reporting_structure['subordinates']) ?> Direct Report<?= count($reporting_structure['subordinates']) > 1 ? 's' : '' ?><br>
                                                <small class="text-muted">
                                                    <?= array_sum(array_column($reporting_structure['subordinates'], 'team_size')) ?> Total Team Members
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organizational Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-project-diagram me-2"></i>Organizational Chart</h5>
                </div>
                <div class="card-body">
                    <div class="org-chart">
                        <?php if (!empty($reporting_structure['org_chart'])): ?>
                            <?php echo $this->renderOrgChart($reporting_structure['org_chart']); ?>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-sitemap fa-3x mb-3"></i>
                                <p>Organizational chart not available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manager Information -->
    <?php if (!empty($reporting_structure['manager'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-tie me-2"></i>Manager Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="manager-details">
                                    <h5 class="mb-3">
                                        <?= htmlspecialchars($reporting_structure['manager']['name']) ?>
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong>Position:</strong>
                                                <?= htmlspecialchars($reporting_structure['manager']['position']) ?>
                                            </p>
                                            <p class="mb-2">
                                                <strong>Department:</strong>
                                                <?= htmlspecialchars($reporting_structure['manager']['department']) ?>
                                            </p>
                                            <p class="mb-2">
                                                <strong>Email:</strong>
                                                <a href="mailto:<?= htmlspecialchars($reporting_structure['manager']['email']) ?>">
                                                    <?= htmlspecialchars($reporting_structure['manager']['email']) ?>
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong>Phone:</strong>
                                                <?= htmlspecialchars($reporting_structure['manager']['phone'] ?? 'N/A') ?>
                                            </p>
                                            <p class="mb-2">
                                                <strong>Experience:</strong>
                                                <?= htmlspecialchars($reporting_structure['manager']['experience'] ?? 'N/A') ?>
                                            </p>
                                            <p class="mb-2">
                                                <strong>Team Size:</strong>
                                                <?= $reporting_structure['manager']['team_size'] ?? 0 ?> members
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="manager-stats">
                                    <h6>Manager's Team Performance</h6>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-success" style="width: <?= $reporting_structure['manager']['team_performance'] ?? 75 ?>%">
                                            <?= $reporting_structure['manager']['team_performance'] ?? 75 ?>%
                                        </div>
                                    </div>
                                    <p class="mb-2">
                                        <strong>Projects:</strong>
                                        <?= $reporting_structure['manager']['active_projects'] ?? 0 ?> Active
                                    </p>
                                    <p class="mb-2">
                                        <strong>Team Satisfaction:</strong>
                                        <span class="badge bg-success">
                                            <?= $reporting_structure['manager']['team_satisfaction'] ?? 'Good' ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Team Members -->
    <?php if (!empty($reporting_structure['subordinates'])): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-users me-2"></i>My Team Members</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($reporting_structure['subordinates'] as $subordinate): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card team-member-card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="team-member-avatar me-3">
                                                    <i class="fas fa-user fa-2x text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <?= htmlspecialchars($subordinate['name']) ?>
                                                    </h6>
                                                    <p class="mb-1 text-muted">
                                                        <?= htmlspecialchars($subordinate['position']) ?>
                                                    </p>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($subordinate['department']) ?>
                                                    </small>
                                                </div>
                                                <div class="team-member-status">
                                                    <?php if ($subordinate['is_active'] ?? true): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="team-member-details mt-3">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted">
                                                            <strong>Joined:</strong><br>
                                                            <?= date('M Y', strtotime($subordinate['joining_date'])) ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">
                                                            <strong>Performance:</strong><br>
                                                            <span class="badge bg-<?= $this->getPerformanceBadgeClass($subordinate['performance_score'] ?? 0) ?>">
                                                                <?= $subordinate['performance_score'] ?? 0 ?>%
                                                            </span>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="team-member-actions mt-3">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewTeamMember(<?= $subordinate['employee_id'] ?>)">
                                                    <i class="fas fa-eye me-1"></i>View Profile
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" onclick="contactTeamMember(<?= $subordinate['employee_id'] ?>)">
                                                    <i class="fas fa-envelope me-1"></i>Contact
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Department Information -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-building me-2"></i>Department Information</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($reporting_structure['department'])): ?>
                        <?php $dept = $reporting_structure['department']; ?>
                        <div class="department-info">
                            <h5 class="mb-3">
                                <?= htmlspecialchars($dept['department_name']) ?>
                            </h5>
                            <p class="mb-2">
                                <strong>Head:</strong>
                                <?= htmlspecialchars($dept['department_head'] ?? 'N/A') ?>
                            </p>
                            <p class="mb-2">
                                <strong>Total Employees:</strong>
                                <?= $dept['total_employees'] ?? 0 ?>
                            </p>
                            <p class="mb-2">
                                <strong>Budget:</strong>
                                ₹<?= number_format($dept['annual_budget'] ?? 0) ?>
                            </p>
                            <p class="mb-3">
                                <strong>Description:</strong><br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($dept['description'] ?? 'No description available.') ?>
                                </small>
                            </p>

                            <h6>Department Goals</h6>
                            <ul class="list-unstyled">
                                <?php foreach (($dept['goals'] ?? []) as $goal): ?>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <?= htmlspecialchars($goal) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No department information available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Department Performance</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($reporting_structure['department'])): ?>
                        <?php $dept = $reporting_structure['department']; ?>
                        <div class="row">
                            <div class="col-6">
                                <div class="metric-item text-center">
                                    <h3 class="text-primary mb-1">
                                        <?= $dept['productivity_score'] ?? 0 ?>%
                                    </h3>
                                    <small class="text-muted">Productivity</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="metric-item text-center">
                                    <h3 class="text-success mb-1">
                                        <?= $dept['satisfaction_score'] ?? 0 ?>%
                                    </h3>
                                    <small class="text-muted">Satisfaction</small>
                                </div>
                            </div>
                        </div>

                        <div class="progress mb-3">
                            <div class="progress-bar bg-info" style="width: <?= $dept['productivity_score'] ?? 0 ?>%">
                                Productivity: <?= $dept['productivity_score'] ?? 0 ?>%
                            </div>
                        </div>

                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" style="width: <?= $dept['satisfaction_score'] ?? 0 ?>%">
                                Satisfaction: <?= $dept['satisfaction_score'] ?? 0 ?>%
                            </div>
                        </div>

                        <h6>Recent Achievements</h6>
                        <ul class="list-unstyled">
                            <?php foreach (($dept['achievements'] ?? []) as $achievement): ?>
                                <li class="mb-2">
                                    <i class="fas fa-trophy text-warning me-2"></i>
                                    <?= htmlspecialchars($achievement) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No department performance data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printStructure() {
    window.print();
}

function exportStructure() {
    // In a real implementation, you would generate and download a PDF or Excel file
    alert('Export functionality would be implemented here.');
}

function viewTeamMember(employeeId) {
    // In a real implementation, you would redirect to the team member's profile
    window.location.href = `/employee/team-member/${employeeId}`;
}

function contactTeamMember(employeeId) {
    // In a real implementation, you would open a contact form or send an email
    window.location.href = `mailto:?subject=Regarding Team Member`;
}

// Auto-refresh data every 10 minutes
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 600000);
</script>

<style>
.org-chart {
    min-height: 300px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.position-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}

.hierarchy-preview {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #dee2e6;
}

.manager-info, .subordinates-info {
    background: #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
}

.manager-avatar, .subordinates-avatar, .team-member-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.team-member-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.team-member-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.team-member-status {
    align-self: flex-start;
}

.team-member-details {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 10px;
}

.team-member-actions .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}

.department-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}

.metric-item {
    padding: 15px;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 10px;
}

.manager-stats {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
}

.badge {
    font-size: 0.8em;
}

@media print {
    .btn, .card-header {
        display: none !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
