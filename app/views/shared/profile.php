<?php
/**
 * Unified Profile View - Works for all user roles
 * Variables expected from controller:
 * - $user: User data array
 * - $userRole: Current user role
 * - $profileUrl: URL for profile form submission
 * - $securityUrl: URL for security/password page
 * - $canEdit: Boolean indicating if user can edit
 */
 
$user = $user ?? [];
$userRole = $userRole ?? $user['role'] ?? 'user';
$profileUrl = $profileUrl ?? BASE_URL . '/admin/profile';
$securityUrl = $securityUrl ?? null;
$canEdit = $canEdit ?? true;
 
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
 
$userName = $user['name'] ?? $user['username'] ?? 'User';
$userEmail = $user['email'] ?? '';
$userPhone = $user['phone'] ?? $user['mobile'] ?? '';
$memberSince = $user['created_at'] ?? date('Y-m-d');
$roleDisplayName = ucwords(str_replace('_', ' ', $userRole));
?>
 
<?php include APP_PATH . '/views/admin/layouts/header.php'; ?>

<div class="page-content">
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
 
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
 
    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div class="avatar-lg mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 32px;">
                        <?php echo strtoupper(substr(trim($userName), 0, 1)); ?>
                    </div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($userName); ?></h5>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($userEmail); ?></p>
                    <span class="badge bg-primary mb-3"><?php echo $roleDisplayName; ?></span>
 
                    <hr class="my-3">
 
                    <div class="text-start">
                        <p class="mb-2"><i class="fas fa-calendar me-2 text-muted"></i><small class="text-muted">Member since</small><br><strong><?php echo date('F Y', strtotime($memberSince)); ?></strong></p>
                        <?php if (!empty($userPhone)): ?>
                            <p class="mb-0"><i class="fas fa-phone me-2 text-muted"></i><strong><?php echo htmlspecialchars($userPhone); ?></strong></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
 
        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $profileUrl; ?>" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" <?php echo $canEdit ? 'required' : 'readonly'; ?>>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" <?php echo $canEdit ? 'required' : 'readonly'; ?>>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($securityUrl): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-shield-alt me-2"></i>Security</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?php echo $securityUrl; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>