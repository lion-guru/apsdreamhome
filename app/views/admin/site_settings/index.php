<?php

/**
 * Site Settings Index View
 * Admin panel for managing site settings
 */

if (!defined('BASE_PATH')) {
    if (session_status() === PHP_SESSION_NONE) {
        // Session started by controller
    }

    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/admin/login');
        exit;
    }
}

$baseUrl = BASE_URL ?? '/apsdreamhome';
$settings = $settings ?? [];

// Start output buffering for layout

$page_title = 'Site Settings';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1"><i class="fas fa-cog me-2 text-primary"></i>Site Settings</h2>
            <p class="text-muted">Manage your website configuration and company information</p>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success'];
                                                    unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error'];
                                                            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo $baseUrl; ?>/admin/settings" id="settingsForm">
        <!-- General Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>General Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="site_name" class="form-control"
                            value="<?php echo htmlspecialchars($settings['general']['site_name']['setting_value'] ?? 'APS Dream Home'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site Description</label>
                        <input type="text" name="site_description" class="form-control"
                            value="<?php echo htmlspecialchars($settings['general']['site_description']['setting_value'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-phone me-2 text-success"></i>Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="contact_phone" class="form-control"
                            value="<?php echo htmlspecialchars($settings['general']['contact_phone']['setting_value'] ?? '+91 92771 21112'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="contact_whatsapp" class="form-control"
                            value="<?php echo htmlspecialchars($settings['general']['contact_whatsapp']['setting_value'] ?? '+91 92771 21112'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="contact_email" class="form-control"
                            value="<?php echo htmlspecialchars($settings['general']['contact_email']['setting_value'] ?? 'info@apsdreamhome.com'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Secondary Phone</label>
                        <input type="text" name="contact_phone2" class="form-control"
                            value="<?php echo htmlspecialchars($settings['general']['contact_phone2']['setting_value'] ?? '+91 70074 44842'); ?>">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Office Address</label>
                        <textarea name="contact_address" class="form-control" rows="2"><?php echo htmlspecialchars($settings['general']['contact_address']['setting_value'] ?? '1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008'); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-share-alt me-2 text-info"></i>Social Media Links</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-facebook me-2 text-primary"></i>Facebook</label>
                        <input type="url" name="social_facebook" class="form-control"
                            value="<?php echo htmlspecialchars($settings['social']['social_facebook']['setting_value'] ?? 'https://www.facebook.com/apsdreamhomes/'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-instagram me-2 text-danger"></i>Instagram</label>
                        <input type="url" name="social_instagram" class="form-control"
                            value="<?php echo htmlspecialchars($settings['social']['social_instagram']['setting_value'] ?? 'https://www.instagram.com/apsdreamhomes/'); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-globe me-2 text-success"></i>JustDial</label>
                        <input type="url" name="social_justdial" class="form-control"
                            value="<?php echo htmlspecialchars($settings['social']['social_justdial']['setting_value'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-briefcase me-2 text-warning"></i>FalconeBiz</label>
                        <input type="url" name="social_falconebiz" class="form-control"
                            value="<?php echo htmlspecialchars($settings['social']['social_falconebiz']['setting_value'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Maps -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Google Maps</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Map Embed URL (Suryoday Colony)</label>
                    <textarea name="map_embed_suryoday" class="form-control" rows="3"><?php echo htmlspecialchars($settings['general']['map_embed_suryoday']['setting_value'] ?? ''); ?></textarea>
                    <small class="text-muted">Paste the Google Maps embed iframe URL here</small>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Save All Settings
            </button>
            <a href="<?php echo $baseUrl; ?>/admin/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
        </div>
    </form>
</div>

<?php


?>