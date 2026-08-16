<?php
$page_title = $page_title ?? 'My Profile - APS Dream Home Agent';
$user = $user ?? [];
$userRole = 'agent';
$profileUrl = BASE_URL . '/agent/profile';
$securityUrl = null;
$canEdit = true;

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$userName = $user['name'] ?? $_SESSION['user_name'] ?? 'Agent';
$userEmail = $user['email'] ?? $_SESSION['user_email'] ?? '';
$userPhone = $user['phone'] ?? '';
$memberSince = $user['created_at'] ?? date('Y-m-d');
$roleDisplayName = 'Agent';
$active_page = 'profile';
?>

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
        <div class="col-lg-4 mb-4">
            <div class="card aps-cp-card">
                <div class="card-body text-center py-5">
                    <?php
                    $userId = (int)($user['id'] ?? $_SESSION['user_id'] ?? 0);
                    $photoUrl = !empty($user['profile_image']) ? BASE_URL . '/' . $user['profile_image'] : null;
                    $size = 'lg';
                    include __DIR__ . '/../shared/profile_photo_upload.php';
                    ?>
                    <h5 class="mb-1 mt-3"><?php echo htmlspecialchars($userName ?? ''); ?></h5>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($userEmail ?? ''); ?></p>
                    <span class="badge bg-primary mb-3"><?php echo $roleDisplayName; ?></span>

                    <hr class="my-3">

                    <div class="text-start">
                        <p class="mb-2"><i class="fas fa-calendar me-2 text-muted"></i><small class="text-muted">Member since</small><br><strong><?php echo date('F Y', strtotime($memberSince)); ?></strong></p>
                        <?php if (!empty($userPhone)): ?>
                            <p class="mb-0"><i class="fas fa-phone me-2 text-muted"></i><strong><?php echo htmlspecialchars($userPhone ?? ''); ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($user['customer_id'])): ?>
                            <p class="mb-0 mt-2"><i class="fas fa-id-card me-2 text-muted"></i><small class="text-muted">Agent ID</small><br><strong><?php echo htmlspecialchars($user['customer_id'] ?? ''); ?></strong></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form action="<?php echo $profileUrl; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? $_SESSION['user_email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? $_SESSION['user_phone'] ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12">
                                
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-shield-alt me-2"></i>Security</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted mb-2">Contact admin to change your password.</p>
                </div>
            </div>
        </div>
    </div>
</div>
