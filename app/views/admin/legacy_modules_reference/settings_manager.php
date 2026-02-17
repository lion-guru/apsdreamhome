<?php
/**
 * System Settings Manager
 * Provides a GUI for admins to manage API keys and other system configurations.
 */

require_once __DIR__ . '/core/init.php';

use App\Core\Database;

// Handle POST requests for updates
$success = '';
$error = '';

$db = \App\Core\App::database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Security validation failed. Please try again.";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_setting') {
        $id = intval($_POST['id']);
        $value = $_POST['setting_value'];
        
        if ($db->execute("UPDATE system_settings SET setting_value = :value WHERE id = :id", ['value' => $value, 'id' => $id])) {
            $success = "Setting updated successfully.";
            // Log the action
            logAdminActivity($db->getConnection(), 'update_setting', "Updated system setting ID: $id");
        } else {
            $error = "Failed to update setting: " . h($db->getConnection()->error);
        }
    }
}

// Fetch all settings grouped by group
$rows = $db->fetchAll("SELECT * FROM system_settings ORDER BY setting_group, display_name");
$settings = [];
if ($rows) {
    foreach ($rows as $row) {
        $settings[$row['setting_group']][] = $row;
    }
}

$page_title = "System Settings";
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">System Settings</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">System Settings</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh Data
                    </button>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo h($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo h($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Info Box -->
        <div class="card bg-primary text-white mb-4 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-4 fs-1">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <h5 class="card-title text-white mb-1">Configuration Control Center</h5>
                    <p class="card-text text-white opacity-75 mb-0">
                        Manage API credentials, payment gateways, and core system parameters without code changes. 
                        <span class="badge bg-warning text-dark ms-2">Restricted Access: IT Manager / CTO / Admin</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Settings Grid -->
        <?php if (empty($settings)): ?>
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <p class="text-muted">No settings found in the database. Run setup first.</p>
            </div>
        <?php else: ?>
            <?php foreach ($settings as $group => $group_settings): ?>
                <div class="group-header mb-3 mt-4">
                    <h4 class="h5">
                        <i class="fas <?php echo $group === 'api' ? 'fa-key' : ($group === 'payment' ? 'fa-credit-card' : 'fa-cog'); ?> me-2"></i>
                        <?php echo h(str_replace('_', ' ', $group)); ?> Configuration
                    </h4>
                </div>
                
                <div class="row">
                    <?php foreach ($group_settings as $setting): ?>
                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card setting-card h-100 shadow-sm border-0">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title m-0 h6 fw-bold"><?php echo h($setting['display_name']); ?></h5>
                                        <?php if ($setting['is_sensitive']): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                <i class="fas fa-lock small"></i> Sensitive
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="card-text mb-4 small text-muted"><?php echo h($setting['description']); ?></p>
                                    
                                    <form method="POST" class="mt-auto">
                                        <?php echo getCsrfField(); ?>
                                        <input type="hidden" name="action" value="update_setting">
                                        <input type="hidden" name="id" value="<?php echo h($setting['id']); ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted mb-1">Parameter Value</label>
                                            <?php if ($setting['is_sensitive']): ?>
                                                <div class="input-group input-group-sm sensitive-group">
                                                    <input type="password" name="setting_value" class="form-control border-end-0" 
                                                           value="<?php echo h($setting['setting_value']); ?>" 
                                                           placeholder="Enter secure value">
                                                    <span class="input-group-text border-start-0 toggle-password cursor-pointer">
                                                        <i class="fas fa-eye text-muted"></i>
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <textarea name="setting_value" class="form-control form-control-sm" rows="3" 
                                                          placeholder="Enter value"><?php echo h($setting['setting_value']); ?></textarea>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-sm btn-update shadow-sm">
                                                <i class="fas fa-save me-1"></i> Apply Changes
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                        <code class="small text-primary"><?php echo h($setting['setting_key']); ?></code>
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            <i class="far fa-clock me-1"></i> <?php echo date('d M, Y H:i', strtotime($setting['updated_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="mt-5 mb-4 text-center text-muted">
            <small>&copy; <?php echo date('Y'); ?> APS Dream Home. All configurations are audited.</small>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

<style>
.cursor-pointer { cursor: pointer; }
.setting-card { transition: transform 0.2s; }
.setting-card:hover { transform: translateY(-3px); }
</style>

<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
</script>


