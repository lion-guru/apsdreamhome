<!-- Users Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">User Management</h1>
        <p class="text-muted mb-0">Manage all system users</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin/users/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add User
    </a>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Users Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4">User</th>
                        <th class="border-0">Email</th>
                        <th class="border-0">Role</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Joined</th>
                        <th class="border-0 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($user['name'] ?? 'Unknown'); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                        <td><span class="badge bg-primary"><?php echo ucfirst($user['role'] ?? 'user'); ?></span></td>
                        <td><span class="badge bg-<?php echo ($user['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($user['status'] ?? 'active'); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'] ?? 'now')); ?></td>
                        <td class="text-end pe-4">
                            <a href="<?php echo BASE_URL; ?>/admin/users/<?php echo $user['id']; ?>/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <a href="<?php echo BASE_URL; ?>/admin/users/<?php echo $user['id']; ?>/destroy" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No users found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
