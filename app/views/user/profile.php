<?php
/**
 * User Profile View
 */
$user = $user ?? [];
$preferences = $preferences ?? [];
$page_title = $page_title ?? 'User Profile - APS Dream Home';
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
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">
    <?php if (!empty($user)): ?>
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="container text-center">
                <img src="<?php echo $user['image'] ?? '/assets/images/default-avatar.jpg'; ?>" alt="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" class="profile-img mb-3">
                <h2 class="mb-1"><?php echo htmlspecialchars($user['name'] ?? ''); ?></h2>
                <p class="mb-0"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                <span class="badge bg-light text-dark mt-2"><?php echo ucfirst($user['role'] ?? 'user'); ?></span>
            </div>
        </div>

        <div class="container py-4">
            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Profile Information</h5>
                            <a href="<?php echo $base; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">Edit Profile</a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <p class="text-muted mb-1">Phone</p>
                                    <p class="mb-0"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p class="text-muted mb-1">Status</p>
                                    <span class="badge bg-<?php echo ($user['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <p class="text-muted mb-1">Member Since</p>
                                    <p class="mb-0"><?php echo isset($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : '-'; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p class="text-muted mb-1">Last Updated</p>
                                    <p class="mb-0"><?php echo isset($user['updated_at']) ? date('M d, Y', strtotime($user['updated_at'])) : '-'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">No recent activity to display</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Quick Actions -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?php echo $base; ?>/users/change-password/<?php echo $user['id']; ?>" class="btn btn-outline-warning">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </a>
                                <a href="<?php echo $base; ?>/customer/settings" class="btn btn-outline-info">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Preferences -->
                    <?php if (!empty($preferences)): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Preferences</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($preferences as $key => $value): ?>
                                        <div class="list-group-item px-0 d-flex justify-content-between">
                                            <small><?php echo ucfirst(str_replace('_', ' ', $key)); ?></small>
                                            <small class="text-muted"><?php echo htmlspecialchars($value); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="container py-5">
            <div class="alert alert-danger">User not found</div>
            <a href="<?php echo $base; ?>/users" class="btn btn-outline-secondary">Back to Users</a>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
