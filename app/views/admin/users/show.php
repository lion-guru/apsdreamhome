<?php
/**
 * User Details View
 */
$user = $user ?? [];
$page_title = $page_title ?? 'User Details';
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
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">User Details</h2>
                <p class="text-muted mb-0">View user information and activity</p>
            </div>
            <a href="<?php echo $base; ?>/admin/users" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Users
            </a>
        </div>
        
        <?php if (!empty($user)): ?>
        <div class="row">
            <!-- User Profile Card -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <div style="width: 100px; height: 100px; border-radius: 50%; background: #4f46e5; color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto;">
                                <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                            </div>
                        </div>
                        <h4 class="mb-1"><?php echo htmlspecialchars($user['name'] ?? 'Unknown'); ?></h4>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                        <span class="badge bg-<?php echo ($user['role'] ?? '') === 'admin' ? 'danger' : (($user['role'] ?? '') === 'associate' ? 'warning' : 'info'); ?>">
                            <?php echo ucfirst($user['role'] ?? 'user'); ?>
                        </span>
                        <span class="badge bg-<?php echo ($user['status'] ?? '') === 'active' ? 'success' : 'secondary'; ?> ms-1">
                            <?php echo ucfirst($user['status'] ?? 'unknown'); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <i class="fas fa-phone me-2 text-muted"></i>
                            <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                            <?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-calendar me-2 text-muted"></i>
                            Joined: <?php echo isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'Unknown'; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Activity Stats -->
            <div class="col-lg-8">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-building fa-2x text-primary mb-2"></i>
                                <h3 class="mb-1"><?php echo $user['property_count'] ?? 0; ?></h3>
                                <p class="text-muted mb-0">Properties</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                                <h3 class="mb-1"><?php echo $user['booking_count'] ?? 0; ?></h3>
                                <p class="text-muted mb-0">Bookings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                <h3 class="mb-1"><?php echo isset($user['last_login']) ? date('M d', strtotime($user['last_login'])) : 'Never'; ?></h3>
                                <p class="text-muted mb-0">Last Login</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <a href="<?php echo $base; ?>/admin/users/<?php echo $user['id']; ?>/edit" class="btn btn-primary w-100">
                                    <i class="fas fa-edit me-2"></i>Edit User
                                </a>
                            </div>
                            <div class="col-md-3">
                                <button onclick="resetPassword(<?php echo $user['id']; ?>)" class="btn btn-warning w-100">
                                    <i class="fas fa-key me-2"></i>Reset Password
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button onclick="toggleStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" class="btn btn-<?php echo ($user['status'] ?? '') === 'active' ? 'secondary' : 'success'; ?> w-100">
                                    <i class="fas fa-<?php echo ($user['status'] ?? '') === 'active' ? 'ban' : 'check'; ?> me-2"></i>
                                    <?php echo ($user['status'] ?? '') === 'active' ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </div>
                            <?php if (($user['role'] ?? '') !== 'admin'): ?>
                            <div class="col-md-3">
                                <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-danger w-100">
                                    <i class="fas fa-trash me-2"></i>Delete
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            User not found or no data available.
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetPassword(userId) {
            if (confirm('Are you sure you want to reset this user\'s password?')) {
                alert('Password reset functionality to be implemented');
            }
        }
        
        function toggleStatus(userId, currentStatus) {
            const action = currentStatus === 'active' ? 'deactivate' : 'activate';
            if (confirm(`Are you sure you want to ${action} this user?`)) {
                alert('Status toggle functionality to be implemented');
            }
        }
        
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                alert('Delete functionality to be implemented');
            }
        }
    </script>
</body>
</html>
