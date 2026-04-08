<?php
/**
 * User Index View - User List
 */
$users = $users ?? [];
$total_count = $total_count ?? 0;
$page_title = $page_title ?? 'Users - APS Dream Home';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">All Users</h2>
                <p class="text-muted mb-0">Total: <?php echo number_format($total_count); ?> users</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/users/create" class="btn btn-primary me-2">
                    <i class="fas fa-plus me-2"></i>Add User
                </a>
                <a href="<?php echo $base; ?>/user/dashboard" class="btn btn-outline-secondary">Dashboard</a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search users...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                            <option value="agent">Agent</option>
                            <option value="associate">Associate</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id'] ?? '-'; ?></td>
                                        <td><?php echo htmlspecialchars($user['name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($user['role'] ?? '') === 'admin' ? 'danger' : (($user['role'] ?? '') === 'agent' ? 'info' : 'secondary'); ?>">
                                                <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo ($user['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : '-'; ?></td>
                                        <td>
                                            <a href="<?php echo $base; ?>/users/show/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary me-1">View</a>
                                            <a href="<?php echo $base; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $user['id']; ?>)">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No users found</p>
                        <a href="<?php echo $base; ?>/users/create" class="btn btn-primary">Create First User</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                alert('Delete user ' + id + ' - This would be implemented with an API call');
            }
        }
    </script>
</body>
</html>
