<?php
/**
 * Edit Site Settings View
 */
$settings_categories = $settings_categories ?? [];
$page_title = $page_title ?? 'Edit Site Settings';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Edit Site Settings</h2>
                <p class="text-muted mb-0">Update website configuration</p>
            </div>
            <a href="<?php echo $base; ?>/admin/settings" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Settings
            </a>
        </div>
        
        <!-- Settings Form -->
        <form id="settingsForm" action="<?php echo $base; ?>/admin/settings/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            
            <div class="row">
                <!-- General Settings -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>General Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Site Name</label>
                                <input type="text" class="form-control" name="site_name" value="APS Dream Home">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Site Description</label>
                                <textarea class="form-control" name="site_description" rows="2">Professional Real Estate Platform</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" class="form-control" name="contact_email" value="info@apsdreamhome.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" name="contact_phone" value="+91-XXXXXXXXXX">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Appearance Settings -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-paint-brush me-2"></i>Appearance Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Logo URL</label>
                                <input type="text" class="form-control" name="logo_url" value="/assets/images/logo.png">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Favicon URL</label>
                                <input type="text" class="form-control" name="favicon_url" value="/assets/images/favicon.ico">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Primary Color</label>
                                <input type="color" class="form-control" name="primary_color" value="#007bff">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Secondary Color</label>
                                <input type="color" class="form-control" name="secondary_color" value="#6c757d">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>Social Media Links</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Facebook</label>
                                <input type="url" class="form-control" name="social_facebook" placeholder="https://facebook.com/...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Twitter</label>
                                <input type="url" class="form-control" name="social_twitter" placeholder="https://twitter.com/...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">LinkedIn</label>
                                <input type="url" class="form-control" name="social_linkedin" placeholder="https://linkedin.com/...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instagram</label>
                                <input type="url" class="form-control" name="social_instagram" placeholder="https://instagram.com/...">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SEO Settings -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-search me-2"></i>SEO Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Site Keywords</label>
                                <input type="text" class="form-control" name="site_keywords" value="real estate, property, dream home">
                                <small class="text-muted">Comma separated keywords</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Site Author</label>
                                <input type="text" class="form-control" name="site_author" value="APS Dream Home Team">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Maintenance Mode</label>
                                <select class="form-select" name="enable_maintenance">
                                    <option value="0">Disabled</option>
                                    <option value="1">Enabled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Maintenance Message</label>
                                <textarea class="form-control" name="maintenance_message" rows="2">Site is under maintenance</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex justify-content-between">
                <a href="<?php echo $base; ?>/admin/settings" class="btn btn-outline-secondary">Cancel</a>
                <div>
                    <button type="button" onclick="resetSettings()" class="btn btn-warning me-2">
                        <i class="fas fa-undo me-2"></i>Reset to Defaults
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetSettings() {
            if (confirm('Are you sure you want to reset all settings to defaults?')) {
                alert('Reset functionality will be implemented');
            }
        }
        
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Settings saved successfully!');
                    window.location.href = '<?php echo $base; ?>/admin/settings';
                } else {
                    alert(data.message || 'Failed to save settings');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving settings');
            });
        });
    </script>
</body>
</html>
