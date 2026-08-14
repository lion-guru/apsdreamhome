<?php

/**
 * Admin Profile View
 */
if (!defined('BASE_PATH')) exit;

$page_title = 'My Profile';
$active_page = 'profile';
$user = $user ?? $user_data ?? [];
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

?>

<div class="page-content">
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card aps-cp-card">
                    <div class="card-body text-center py-5">
                        <?php
                        $userId = (int)($user['id'] ?? $_SESSION['admin_id'] ?? 0);
                        $photoUrl = !empty($user['profile_image']) ? BASE_URL . '/' . $user['profile_image'] : null;
                        $userName = $user['name'] ?? $user['username'] ?? 'Admin';
                        $size = 'lg';
                        include APP_PATH . '/views/shared/profile_photo_upload.php';
                        ?>
                        <h5 class="mb-1 mt-3"><?php echo htmlspecialchars($user['name'] ?? $user['username'] ?? 'User'); ?></h5>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                        <span class="role-badge role-<?php echo $user['role'] ?? 'admin'; ?>">
                            <?php echo ucwords(str_replace('_', ' ', $user['role'] ?? 'Admin')); ?>
                        </span>

                        <hr class="my-4">

                        <div class="text-start">
                            <p class="mb-2">
                                <i class="fas fa-calendar me-2 text-muted"></i>
                                <small class="text-muted">Member since</small><br>
                                <strong><?php echo date('F Y', strtotime($user['created_at'] ?? date('Y-m-d'))); ?></strong>
                            </p>
                            <?php if (!empty($user['phone'])): ?>
                                <p class="mb-0">
                                    <i class="fas fa-phone me-2 text-muted"></i>
                                    <strong><?php echo htmlspecialchars($user['phone']); ?></strong>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card aps-cp-card">
                    <div class="card-header aps-cp-card-header">
                        <h5 class="card-title"><i class="fas fa-user me-2"></i>Profile Information</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <form action="<?php echo BASE_URL; ?>/admin/profile" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
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
                        <h5 class="card-title"><i class="fas fa-shield-alt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <a href="<?php echo BASE_URL; ?>/admin/profile/security" class="btn btn-outline-primary">
                            <i class="fas fa-key me-2"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php /* footer included via layouts/admin.php */ ?>