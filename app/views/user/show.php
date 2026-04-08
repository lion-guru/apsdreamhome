<?php
/**
 * User Show/Details View
 */
$user = $user ?? [];
$preferences = $preferences ?? [];
$page_title = $page_title ?? 'User Details - APS Dream Home';
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
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (!empty($user)): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-user me-2"></i>User Details</h4>
                            <div>
                                <a href="<?php echo $base; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-light me-2">Edit</a>
                                <a href="<?php echo $base; ?>/users" class="btn btn-sm btn-outline-light">Back</a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Full Name</p>
                                    <h5><?php echo htmlspecialchars($user['name'] ?? '-'); ?></h5>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">User ID</p>
                                    <h5>#<?php echo $user['id'] ?? '-'; ?></h5>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Email</p>
                                    <p><?php echo htmlspecialchars($user['email'] ?? '-'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Phone</p>
                                    <p><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Role</p>
                                    <span class="badge bg-<?php echo ($user['role'] ?? '') === 'admin' ? 'danger' : 'info'; ?>">
                                        <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Status</p>
                                    <span class="badge bg-<?php echo ($user['status'] ?? '') === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                    </span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Created At</p>
                                    <p><?php echo isset($user['created_at']) ? date('M d, Y H:i', strtotime($user['created_at'])) : '-'; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Last Updated</p>
                                    <p><?php echo isset($user['updated_at']) ? date('M d, Y H:i', strtotime($user['updated_at'])) : '-'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preferences -->
                    <?php if (!empty($preferences)): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>User Preferences</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($preferences as $key => $value): ?>
                                        <div class="list-group-item d-flex justify-content-between">
                                            <span><?php echo ucfirst(str_replace('_', ' ', $key)); ?></span>
                                            <span class="text-muted"><?php echo htmlspecialchars($value); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-danger">User not found</div>
                    <a href="<?php echo $base; ?>/users" class="btn btn-outline-secondary">Back to Users</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
