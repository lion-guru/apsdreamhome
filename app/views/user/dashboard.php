<?php
/**
 * User Dashboard View
 */
$users = $users ?? [];
$active_users = $active_users ?? [];
$statistics = $statistics ?? [];
$total_users = $total_users ?? 0;
$active_count = $active_count ?? 0;
$page_title = $page_title ?? 'User Dashboard - APS Dream Home';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">User Dashboard</h2>
                <p class="text-muted mb-0">Manage system users and view statistics</p>
            </div>
            <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back to Admin</a>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4><?php echo number_format($total_users); ?></h4>
                        <p class="text-muted mb-0">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                        <h4><?php echo number_format($active_count); ?></h4>
                        <p class="text-muted mb-0">Active Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-user-plus fa-2x text-info mb-2"></i>
                        <h4><?php echo number_format($total_users - $active_count); ?></h4>
                        <p class="text-muted mb-0">Inactive Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                        <h4><?php echo !empty($statistics) ? count($statistics) : 0; ?></h4>
                        <p class="text-muted mb-0">Statistics</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Management</h5>
                    <a href="<?php echo $base; ?>/users/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New User
                    </a>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Users</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover table-responsive">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($users, 0, 10) as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id'] ?? '-'; ?></td>
                                        <td><?php echo htmlspecialchars($user['name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($user['role'] ?? 'user') === 'admin' ? 'danger' : 'info'; ?>">
                                                <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo ($user['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo $base; ?>/users/show/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="<?php echo $base; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo $base; ?>/users" class="btn btn-outline-primary">View All Users</a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No users found in the system</p>
                        <a href="<?php echo $base; ?>/users/create" class="btn btn-primary">Create First User</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
