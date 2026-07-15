<?php
$page_title = 'My Profile - APS Dream Home';
$current_page = 'farmer-profile';
$extraHead = '<style>
.profile-card-f { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
</style>';
$farmer = $farmer ?? [];
$profile = $profile ?? [];
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-cog text-primary me-2"></i>My Profile</h4>
        <a href="<?php echo BASE_URL; ?>/farmer/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card profile-card-f">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-user-circle text-info me-2"></i>Profile Photo</h5>
                </div>
                <div class="card-body text-center py-4">
                    <?php
                    $userId = (int)($farmer['id'] ?? $_SESSION['user_id'] ?? 0);
                    $photoUrl = !empty($farmer['profile_image']) ? BASE_URL . '/' . $farmer['profile_image'] : null;
                    $userName = $farmer['name'] ?? 'Farmer';
                    $size = 'lg';
                    include __DIR__ . '/../shared/profile_photo_upload.php';
                    ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card profile-card-f">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-edit text-success me-2"></i>Edit Profile</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>Profile updated successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($farmer['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control" value="<?php echo htmlspecialchars($farmer['phone'] ?? ''); ?>" disabled>
                                <small class="text-muted">Phone cannot be changed</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($farmer['email'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($farmer['address'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card profile-card-f mb-3">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-id-card text-info me-2"></i>KYC Details</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if ($profile): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Aadhar Number</small>
                        <strong><?php echo htmlspecialchars($profile['aadhar_number'] ?? 'N/A'); ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">PAN Number</small>
                        <strong><?php echo htmlspecialchars($profile['pan_number'] ?? 'N/A'); ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Bank Account</small>
                        <strong><?php echo htmlspecialchars($profile['bank_account'] ?? 'N/A'); ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">IFSC Code</small>
                        <strong><?php echo htmlspecialchars($profile['ifsc'] ?? $profile['ifsc_code'] ?? 'N/A'); ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Account Holder</small>
                        <strong><?php echo htmlspecialchars($profile['account_holder_name'] ?? 'N/A'); ?></strong>
                    </div>
                    <?php else: ?>
                    <p class="text-muted text-center py-3">No KYC details available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
