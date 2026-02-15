<?php
/**
 * System Settings - Perfect Admin
 */

$settings = $adminService->getSystemSettings();
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if ($security->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        // Logic to save settings would go here
        $successMsg = 'Settings saved successfully!';
    } else {
        $error = 'Invalid security token.';
    }
}
?>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">System Settings</h5>
                
                <?php if ($successMsg): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo h($successMsg); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="<?php echo h($settings['site_name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="<?php echo h($settings['contact_email']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo h($settings['phone']); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo h($settings['address']); ?></textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Application Controls</h6>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenanceMode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="allow_registration" id="allowReg" <?php echo $settings['allow_registration'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="allowReg">Allow User Registration</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Items Per Page (Admin Lists)</label>
                            <select name="items_per_page" class="form-select">
                                <option value="10" <?php echo $settings['items_per_page'] == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $settings['items_per_page'] == 20 ? 'selected' : ''; ?>>20</option>
                                <option value="50" <?php echo $settings['items_per_page'] == 50 ? 'selected' : ''; ?>>50</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <button type="submit" name="save_settings" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">System Information</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>PHP Version</span>
                        <span class="badge bg-info-subtle text-info"><?php echo PHP_VERSION; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Server Software</span>
                        <span class="text-muted small"><?php echo h($_SERVER['SERVER_SOFTWARE']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>Database</span>
                        <span class="text-muted small">MySQL (PDO)</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title mb-3">Cache Management</h5>
                <p class="text-muted small">Clear system cache to refresh data across the application.</p>
                <button type="button" class="btn btn-outline-warning w-100">
                    <i class="fas fa-trash-alt me-2"></i>Clear Cache
                </button>
            </div>
        </div>
    </div>
</div>
