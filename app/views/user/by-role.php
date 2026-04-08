<?php
/**
 * Users By Role View
 */
$users = $users ?? [];
$role = $role ?? 'user';
$total_count = $total_count ?? 0;
$page_title = $page_title ?? ucfirst($role) . ' Users - APS Dream Home';
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
                <h2 class="mb-1"><?php echo ucfirst($role); ?> Users</h2>
                <p class="text-muted mb-0">Total: <?php echo number_format($total_count); ?> users with <?php echo strtolower($role); ?> role</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/users" class="btn btn-outline-secondary me-2">All Users</a>
                <a href="<?php echo $base; ?>/users/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add User
                </a>
            </div>
        </div>

        <!-- Role Filter -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="<?php echo $base; ?>/users/by-role/admin" class="btn btn-outline-<?php echo $role === 'admin' ? 'danger active' : 'secondary'; ?>">Admin</a>
                    <a href="<?php echo $base; ?>/users/by-role/agent" class="btn btn-outline-<?php echo $role === 'agent' ? 'info active' : 'secondary'; ?>">Agent</a>
                    <a href="<?php echo $base; ?>/users/by-role/associate" class="btn btn-outline-<?php echo $role === 'associate' ? 'primary active' : 'secondary'; ?>">Associate</a>
                    <a href="<?php echo $base; ?>/users/by-role/user" class="btn btn-outline-<?php echo $role === 'user' ? 'success active' : 'secondary'; ?>">User</a>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><?php echo ucfirst($role); ?> List</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
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
                                        <td>
                                            <span class="badge bg-<?php echo ($user['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : '-'; ?></td>
                                        <td>
                                            <a href="<?php echo $base; ?>/users/show/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="<?php echo $base; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No <?php echo strtolower($role); ?> users found</p>
                        <a href="<?php echo $base; ?>/users/create" class="btn btn-primary">Create <?php echo ucfirst($role); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
