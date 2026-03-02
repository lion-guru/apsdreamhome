<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-3">
                        <a href="<?php echo BASE_URL; ?>dashboard" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="<?php echo BASE_URL; ?>dashboard/profile" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-user me-2"></i> My Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>dashboard/settings" class="list-group-item list-group-item-action active py-3">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                        <a href="<?php echo BASE_URL; ?>notifications" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </a>
                        <form action="<?php echo BASE_URL; ?>logout" method="POST" class="d-inline">
                            <button type="submit" class="list-group-item list-group-item-action py-3 text-danger border-0 w-100 text-start">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <h2 class="mb-4">Account Settings</h2>
            
            <?php if (isset($data['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $data['success']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($data['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $data['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Privacy & Security</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>dashboard/settings/update-security" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Email Notifications</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                                <label class="form-check-label" for="emailNotif">Receive updates about new properties and projects</label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Two-Factor Authentication</label>
                            <p class="text-muted small">Add an extra layer of security to your account.</p>
                            <button type="button" class="btn btn-outline-primary btn-sm">Enable 2FA</button>
                        </div>

                        <hr>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3 border-danger-subtle">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="mb-0">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Once you delete your account, there is no going back. Please be certain.</p>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        Delete My Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete your account? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo BASE_URL; ?>dashboard/delete-account" method="POST">
                    <button type="submit" class="btn btn-danger">Yes, Delete Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
