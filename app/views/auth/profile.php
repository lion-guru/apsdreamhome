<?php $pageTitle = 'My Profile'; ?>
<?php $user = $user ?? []; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Home</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>user/dashboard">Dashboard</a></li><li class="breadcrumb-item active">Profile</li></ol></nav>
    <?php if (empty($user)): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i><h6 class="text-muted">User not found</h6><p class="text-muted small">Please login to view your profile.</p><a href="<?= BASE_URL ?>login" class="btn btn-primary btn-sm"><i class="fas fa-sign-in-alt me-1"></i>Login</a></div></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3"><div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:100px;height:100px;font-size:2.5rem"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div></div>
                    <h5><?= htmlspecialchars($user['name'] ?? '-') ?></h5>
                    <p class="text-muted small mb-1"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($user['email'] ?? '-') ?></p>
                    <p class="text-muted small mb-0"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($user['phone'] ?? '-') ?></p>
                    <hr>
                    <a href="<?= BASE_URL ?>user/dashboard" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Profile</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>user/profile">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                        <hr>
                        <h6>Change Password</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" placeholder="Leave blank to keep">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" placeholder="Min 6 characters">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="Repeat new password">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Profile</button>
                    </form>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Info</h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <tr><th>Member Since</th><td><?= htmlspecialchars($user['created_at'] ?? 'N/A') ?></td></tr>
                        <tr><th>Account Status</th><td><span class="badge bg-success"><?= htmlspecialchars(ucfirst($user['status'] ?? 'Active')) ?></span></td></tr>
                        <tr><th>Last Login</th><td><?= htmlspecialchars($user['last_login'] ?? 'N/A') ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
