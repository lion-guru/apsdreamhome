<?php
$page_title = __('user_profile_title') . ' - APS Dream Home';
$extraHead = '<style>
    .profile-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .social-account-card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 1rem; transition: all 0.2s; }
    .social-account-card:hover { border-color: #0d9488; box-shadow: 0 4px 12px rgba(13,148,136,0.1); }
    .social-account-card .provider-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: white; }
    .social-account-card .provider-icon.google { background: linear-gradient(135deg, #ea4335, #fbbc05); }
    .social-account-card .provider-icon.facebook { background: linear-gradient(135deg, #1877f2, #42a5f5); }
    .social-account-card .provider-icon.linkedin { background: linear-gradient(135deg, #0a66c2, #3b82f6); }
    .social-btn { width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.75rem; padding: 1rem; border: 2px dashed #e2e8f0; border-radius: 12px; background: #f8fafc; color: #475569; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .social-btn:hover { border-color: #0d9488; background: #f0fdfa; color: #0d9488; }
    .social-btn.google { color: #ea4335; }
    .social-btn.facebook { color: #1877f2; }
    .social-btn.linkedin { color: #0a66c2; }
</style>';
?>

<div class="content-area p-4">
    <div class="row">
        <div class="col-lg-9">
            <div class="card profile-card">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="fas fa-user-cog me-2 text-primary"></i><?= __('user_profile_heading') ?></h4>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error ?? ''); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?= __('user_profile_updated') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_name') ?> *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_email') ?></label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                                <small class="text-muted"><?= __('user_profile_email_locked') ?></small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_phone') ?> *</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_member_since') ?></label>
                                <input type="text" class="form-control" value="<?php echo date('d M Y', strtotime($user['created_at'] ?? 'now')); ?>" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3"><?= __('user_profile_password_heading') ?></h5>
                        <p class="text-muted small mb-3"><?= __('user_profile_password_hint') ?></p>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_new_password') ?></label>
                                <input type="password" name="new_password" class="form-control" placeholder="<?= __('user_profile_ph_new_password') ?>" minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('user_profile_label_confirm_password') ?></label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="<?= __('user_profile_ph_confirm_password') ?>">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?= __('user_profile_button_save') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4 profile-card">
                <div class="card-body aps-cp-card-body">
                    <h5 class="mb-3"><i class="fas fa-shield-alt me-2 text-danger"></i><?= __('user_profile_security_heading') ?></h5>
                    <p class="text-muted"><?= __('user_profile_security_desc') ?></p>
                    <a href="<?php echo BASE_URL; ?>/user/logout" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i><?= __('user_profile_button_logout') ?>
                    </a>
                </div>
            </div>

            <!-- Social Accounts Section -->
            <div class="card mt-4 profile-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-link me-2 text-primary"></i>Linked Social Accounts</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($social_accounts)): ?>
                        <?php foreach ($social_accounts as $account): ?>
                            <div class="social-account-card">
                                <div class="provider-icon <?= htmlspecialchars($account['provider']) ?>">
                                    <i class="fab fa-<?= $account['provider'] === 'google' ? 'google' : ($account['provider'] === 'facebook' ? 'facebook-f' : 'linkedin-in') ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= ucfirst($account['provider']) ?></h6>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($account['provider_email'] ?? 'Connected') ?>
                                        <br>
                                        <span class="badge bg-success">Active</span>
                                    </small>
                                </div>
                                <form method="POST" action="<?= BASE_URL ?>/user/social/unlink" style="display:inline;" onsubmit="return confirm('Unlink this account? You will need password to login.');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="provider" value="<?= htmlspecialchars($account['provider']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-unlink me-1"></i> Unlink
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center mb-3">No social accounts linked yet.</p>
                    <?php endif; ?>
                    
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <a href="<?= BASE_URL ?>/auth/google" class="social-btn google">
                                <i class="fab fa-google"></i> Link Google
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= BASE_URL ?>/auth/facebook" class="social-btn facebook">
                                <i class="fab fa-facebook-f"></i> Link Facebook
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= BASE_URL ?>/auth/linkedin" class="social-btn linkedin">
                                <i class="fab fa-linkedin-in"></i> Link LinkedIn
                            </a>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3 text-center">
                        <i class="fas fa-info-circle me-1"></i>
                        Link social accounts to login quickly without password. 
                        If you forget your password, you can use linked Google/Facebook to recover access.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
