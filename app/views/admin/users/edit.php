<?php
$user = $user ?? [];
$roles = $roles ?? ['admin', 'manager', 'associate', 'agent', 'customer', 'user'];
$page_title = $page_title ?? 'Edit User';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Edit User</h2>
                <p class="text-muted mb-0">Update user information</p>
            </div>
            <a href="<?php echo $base; ?>/admin/users" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Users
            </a>
        </div>
        
        <?php if (!empty($user)): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form id="editUserForm" action="<?php echo $base; ?>/admin/users/<?php echo $user['id']; ?>/update" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="role" name="role" required>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role; ?>" <?php echo ($user['role'] ?? '') === $role ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($role); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo ($user['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($user['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="suspended" <?php echo ($user['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current">
                                    <small class="text-muted">Only fill if you want to change password</small>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo $base; ?>/admin/users" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            User not found.
        </div>
        <?php endif; ?>
    </div>
    
    
    <script>
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // Non-AJAX response - redirect to users list
                    window.location.href = '<?php echo $base; ?>/admin/users';
                    return { success: true };
                }
            })
            .then(data => {
                if (data && data.success) {
                    showToast('User updated successfully!', 'success');
                    window.location.href = '<?php echo $base; ?>/admin/users';
                } else if (data) {
                    showToast(data.message || 'Failed to update user', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // On network error, try redirecting anyway
                window.location.href = '<?php echo $base; ?>/admin/users';
            });
        });
    </script>

