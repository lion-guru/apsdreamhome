<?php
/**
 * Users Management Template
 * Admin interface for managing users
 */

?>

<!-- Admin Header -->
<section class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    Users Management
                </h1>
                <p class="mb-0 opacity-75">Manage all user accounts in your system</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a href="<?php echo BASE_URL; ?>admin/users/create" class="btn btn-light btn-lg">
                    <i class="fas fa-plus me-2"></i>Add New User
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Users Management -->
<section class="users-management py-5">
    <div class="container">
        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-card">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label fw-medium">
                                <i class="fas fa-search text-primary me-2"></i>
                                Search Users
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="search"
                                   name="search"
                                   placeholder="Search by name, email, or phone"
                                   value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="role" class="form-label fw-medium">
                                <i class="fas fa-user-tag text-primary me-2"></i>
                                Role
                            </label>
                            <select class="form-select" id="role" name="role">
                                <option value="">All Roles</option>
                                <option value="customer" <?php echo ($filters['role'] ?? '') === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                <option value="agent" <?php echo ($filters['role'] ?? '') === 'agent' ? 'selected' : ''; ?>>Agent</option>
                                <option value="admin" <?php echo ($filters['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="status" class="form-label fw-medium">
                                <i class="fas fa-toggle-on text-primary me-2"></i>
                                Status
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="suspended" <?php echo ($filters['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-medium">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <!-- Table Header -->
                    <div class="table-header d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-list text-primary me-2"></i>
                                Users (<?php echo number_format($total_users ?? 0); ?>)
                            </h5>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center">
                                <label for="per_page" class="form-label mb-0 me-2">Show:</label>
                                <select class="form-select form-select-sm" id="per_page" name="per_page"
                                        onchange="window.location.href = this.value">
                                    <option value="<?php echo BASE_URL; ?>admin/users?<?php echo http_build_query(array_merge($_GET, ['per_page' => 10])); ?>" <?php echo ($filters['per_page'] ?? 10) == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="<?php echo BASE_URL; ?>admin/users?<?php echo http_build_query(array_merge($_GET, ['per_page' => 25])); ?>" <?php echo ($filters['per_page'] ?? 10) == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="<?php echo BASE_URL; ?>admin/users?<?php echo http_build_query(array_merge($_GET, ['per_page' => 50])); ?>" <?php echo ($filters['per_page'] ?? 10) == 50 ? 'selected' : ''; ?>>50</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" class="form-check-input me-2" id="selectAll">
                                            <span>User</span>
                                        </div>
                                    </th>
                                    <th>
                                        <a href="#" class="text-decoration-none text-dark"
                                           onclick="sortTable('name')">
                                            Name <i class="fas fa-sort"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="#" class="text-decoration-none text-dark"
                                           onclick="sortTable('email')">
                                            Email <i class="fas fa-sort"></i>
                                        </a>
                                    </th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Properties</th>
                                    <th>Last Login</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="no-data">
                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No users found</h5>
                                                <p class="text-muted">Try adjusting your search or filter criteria</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input me-2 user-checkbox" value="<?php echo $user['id']; ?>">
                                                    <div class="user-avatar">
                                                        <div class="avatar-circle bg-primary text-white">
                                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <div class="fw-medium"><?php echo htmlspecialchars($user['name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo match($user['role']) {
                                                        'admin' => 'danger',
                                                        'agent' => 'warning',
                                                        default => 'primary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($user['role'] ?? 'customer'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo match($user['status']) {
                                                        'active' => 'success',
                                                        'inactive' => 'secondary',
                                                        'suspended' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-medium text-primary">
                                                    <?php echo number_format($user['properties_count'] ?? 0); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['last_login']): ?>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($user['last_login'])); ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo BASE_URL; ?>admin/users/edit/<?php echo $user['id']; ?>"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')"
                                                            title="Delete User">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-wrapper">
                            <nav aria-label="Users pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($filters['page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="<?php echo BASE_URL; ?>admin/users?<?php echo http_build_query(array_merge($_GET, ['page' => $filters['page'] - 1])); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start_page = max(1, $filters['page'] - 2);
                                    $end_page = min($total_pages, $filters['page'] + 2);

                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i === $filters['page'] ? 'active' : ''; ?>">
                                            <a class="page-link"
                                               href="<?php echo BASE_URL; ?>admin/users?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($filters['page'] < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="<?php echo BASE_URL; ?>admin/users?<?php echo http_build_query(array_merge($_GET, ['page' => $filters['page'] + 1])); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cogs me-2"></i>
                    Bulk Actions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Select users and choose an action to perform:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-warning" onclick="bulkAction('activate')">
                        <i class="fas fa-check-circle me-2"></i>
                        Activate Selected Users
                    </button>
                    <button type="button" class="btn btn-danger" onclick="bulkAction('deactivate')">
                        <i class="fas fa-ban me-2"></i>
                        Deactivate Selected Users
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-2"></i>
                        Delete Selected Users
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

// Sort table functionality
function sortTable(column) {
    const currentSort = '<?php echo $filters['sort'] ?? 'created_at'; ?>';
    const currentOrder = '<?php echo $filters['order'] ?? 'DESC'; ?>';

    let newOrder = 'ASC';
    if (column === currentSort && currentOrder === 'ASC') {
        newOrder = 'DESC';
    }

    const params = new URLSearchParams(window.location.search);
    params.set('sort', column);
    params.set('order', newOrder);
    params.set('page', '1'); // Reset to first page

    window.location.href = '<?php echo BASE_URL; ?>admin/users?' + params.toString();
}

// Delete user confirmation
function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"?\n\nThis action cannot be undone.`)) {
        // Create a form and submit it to the delete endpoint
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?php echo BASE_URL; ?>admin/users/delete/${userId}`;

        // Add CSRF token if needed
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo $_SESSION['csrf_token'] ?? 'token'; ?>';
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }
}

// Bulk actions functionality
function bulkAction(action) {
    const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
    if (selectedUsers.length === 0) {
        alert('Please select at least one user');
        return;
    }

    const userIds = Array.from(selectedUsers).map(cb => cb.value);

    if (confirm(`Are you sure you want to ${action} ${selectedUsers.length} selected users?`)) {
        // Create a form and submit it to the bulk action endpoint
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?php echo BASE_URL; ?>admin/users/bulk-${action}`;

        // Add user IDs
        userIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        // Add CSRF token if needed
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo $_SESSION['csrf_token'] ?? 'token'; ?>';
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('bulkActionsModal'));
    modal.hide();
}

// Show bulk actions modal when users are selected
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const bulkActionsBtn = document.querySelector('[data-bs-target="#bulkActionsModal"]');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
            if (selectedCount > 0 && bulkActionsBtn) {
                bulkActionsBtn.style.display = 'inline-block';
            } else if (bulkActionsBtn) {
                bulkActionsBtn.style.display = 'none';
            }
        });
    });
});
</script>
