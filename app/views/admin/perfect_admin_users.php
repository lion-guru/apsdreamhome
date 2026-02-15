<?php
/**
 * Perfect Admin - User Management Content
 * This file is included by admin.php and enhanced_admin_system.php
 */

if (!isset($adminService)) {
    $adminService = new PerfectAdminService();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filters = [
    'search' => $_GET['search'] ?? '',
    'role' => $_GET['role'] ?? '',
    'status' => $_GET['status'] ?? ''
];

$userData = $adminService->getUserList($filters, $page);
$users = $userData['users'];
$totalPages = $userData['total_pages'];
$totalCount = $userData['total_count'];
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">User Management</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-2"></i>Add New User
        </button>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="row g-3 mb-4">
            <input type="hidden" name="action" value="users">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search users..." value="<?php echo h($filters['search']); ?>">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="1" <?php echo $filters['role'] == '1' ? 'selected' : ''; ?>>Admin</option>
                    <option value="2" <?php echo $filters['role'] == '2' ? 'selected' : ''; ?>>Agent</option>
                    <option value="3" <?php echo $filters['role'] == '3' ? 'selected' : ''; ?>>Customer</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $filters['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $filters['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No users found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-bold"><?php echo h($user['uname']); ?></div>
                                            <small class="text-muted">ID: #<?php echo h($user['uid']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope me-2 small opacity-50"></i><?php echo h($user['uemail']); ?></div>
                                    <div><i class="fas fa-phone me-2 small opacity-50"></i><?php echo h($user['uphone']); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <?php echo h($user['role_name'] ?? 'User'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_updated'] == 'active' || $user['is_updated'] == '1'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo h($user['created_date']); ?></td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2 opacity-50"></i>View Details</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2 opacity-50"></i>Edit User</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt me-2 opacity-50"></i>Delete User</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=users&page=<?php echo h($page - 1); ?>&search=<?php echo h($filters['search']); ?>&role=<?php echo h($filters['role']); ?>&status=<?php echo h($filters['status']); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?action=users&page=<?php echo h($i); ?>&search=<?php echo h($filters['search']); ?>&role=<?php echo h($filters['role']); ?>&status=<?php echo h($filters['status']); ?>"><?php echo h($i); ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=users&page=<?php echo h($page + 1); ?>&search=<?php echo h($filters['search']); ?>&role=<?php echo h($filters['role']); ?>&status=<?php echo h($filters['status']); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
