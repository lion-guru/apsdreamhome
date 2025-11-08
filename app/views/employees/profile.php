<?php
/**
 * Employee Profile View
 * Shows employee profile information and recent activities
 */
?>

<div class="container-fluid">
    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user me-2"></i>My Profile</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="profile-avatar mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <h4><?= htmlspecialchars($employee['name']) ?></h4>
                            <p class="text-muted">
                                <?= htmlspecialchars($employee['role_name']) ?><br>
                                <?= htmlspecialchars($employee['department_name']) ?>
                            </p>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Personal Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td><?= htmlspecialchars($employee['email']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td><?= htmlspecialchars($employee['phone'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Joining Date:</strong></td>
                                            <td><?= htmlspecialchars($employee['joining_date'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Last Login:</strong></td>
                                            <td><?= htmlspecialchars($employee['last_login'] ?? 'N/A') ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Address Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Address:</strong></td>
                                            <td><?= htmlspecialchars($employee['address'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>City:</strong></td>
                                            <td><?= htmlspecialchars($employee['city'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>State:</strong></td>
                                            <td><?= htmlspecialchars($employee['state'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pincode:</strong></td>
                                            <td><?= htmlspecialchars($employee['pincode'] ?? 'N/A') ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-clock me-2"></i>Recent Activities</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($activities)): ?>
                        <p class="text-muted">No recent activities found.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($activities as $activity): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">
                                            <?= htmlspecialchars($activity['activity_type']) ?>
                                        </h6>
                                        <p class="timeline-text">
                                            <?= htmlspecialchars($activity['description'] ?? '') ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= htmlspecialchars($activity['created_at']) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="stats-item">
                        <div class="stats-icon bg-primary">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stats-info">
                            <h4><?= count($tasks) ?></h4>
                            <p>Active Tasks</p>
                        </div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stats-info">
                            <h4><?= count(array_filter($tasks, function($t) { return $t['status'] === 'completed'; })) ?></h4>
                            <p>Completed Tasks</p>
                        </div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-icon bg-warning">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stats-info">
                            <h4><?= count($attendance) ?></h4>
                            <p>Days Present</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="/employee/tasks" class="btn btn-primary btn-sm btn-block mb-2">
                        <i class="fas fa-tasks me-2"></i>View Tasks
                    </a>
                    <a href="/employee/attendance" class="btn btn-success btn-sm btn-block mb-2">
                        <i class="fas fa-calendar-check me-2"></i>View Attendance
                    </a>
                    <a href="/employee/leaves" class="btn btn-warning btn-sm btn-block mb-2">
                        <i class="fas fa-calendar-alt me-2"></i>Apply Leave
                    </a>
                    <button type="button" class="btn btn-info btn-sm btn-block" onclick="changePassword()">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/employee/change-password" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function changePassword() {
    $('#changePasswordModal').modal('show');
}
</script>
