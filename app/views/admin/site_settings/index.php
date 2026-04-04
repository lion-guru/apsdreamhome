<?php
/**
 * Site Settings Index View
 * Admin panel for managing site settings
 */
$baseUrl = BASE_URL ?? '/apsdreamhome';
$settings = $settings ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Site Settings'; ?> | APS Dream Home Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --sidebar-bg: #1e1b4b;
            --sidebar-text: #e0e7ff;
            --main-bg: #f8fafc;
        }
        body { font-family: 'Inter', sans-serif; background: var(--main-bg); }
        .sidebar {
            position: fixed; top: 0; left: 0; width: 260px; height: 100vh;
            background: var(--sidebar-bg); z-index: 1000; overflow-y: auto;
        }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo { color: #fff; font-size: 1.1rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .sidebar-logo i { color: #a5b4fc; }
        .sidebar-sec { padding: 15px 15px 5px; font-size: .65rem; text-transform: uppercase; color: rgba(255,255,255,0.35); font-weight: 600; }
        .sidebar-menu { list-style: none; padding: 0 10px; margin: 0; }
        .sidebar-item { margin-bottom: 2px; }
        .sidebar-link { display: flex; align-items: center; padding: 9px 12px; color: #c7d2fe; text-decoration: none; border-radius: 8px; font-size: .85rem; font-weight: 500; transition: all .2s; }
        .sidebar-link:hover, .sidebar-link.active { background: #312e81; color: #fff; }
        .sidebar-link i { width: 20px; margin-right: 10px; font-size: .95rem; color: #a5b4fc; }
        .sidebar-link.active i, .sidebar-link:hover i { color: #fff; }
        .main-content { margin-left: 260px; min-height: 100vh; }
        .top-nav { background: #fff; height: 60px; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; }
        .page-content { padding: 24px; }
        .settings-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 20px; }
        .settings-card-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
        .settings-card-title { font-size: 1rem; font-weight: 600; color: #1e293b; margin: 0; }
        .settings-card-body { padding: 20px; }
        .form-label { font-size: .875rem; font-weight: 500; color: #374151; }
        .form-control { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: .875rem; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .btn-primary { background: var(--primary); border-color: var(--primary); padding: 10px 20px; font-weight: 500; }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .setting-item { margin-bottom: 20px; }
        .setting-key { font-size: .75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        @media(max-width:991px){
            .sidebar{transform:translateX(-100%)}
            .sidebar.show{transform:translateX(0)}
            .main-content{margin-left:0}
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo $baseUrl; ?>/admin/dashboard" class="sidebar-logo">
                <i class="fas fa-home"></i><span>APS Dream Home</span>
            </a>
        </div>
        <div class="sidebar-sec">Main</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $baseUrl; ?>/admin/dashboard" class="sidebar-link">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
            </li>
        </ul>
        <div class="sidebar-sec">Settings</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $baseUrl; ?>/admin/settings" class="sidebar-link active">
                    <i class="fas fa-cog"></i> Site Settings
                </a>
            </li>
        </ul>
        <div class="sidebar-sec">Account</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?php echo $baseUrl; ?>/admin/logout" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <nav class="top-nav">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="fas fa-bars"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo $baseUrl; ?>/admin/dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Site Settings</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">Welcome, Admin</span>
                <a href="<?php echo $baseUrl; ?>/admin/logout" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>

        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 fw-bold">Site Settings</h1>
                    <p class="text-muted mb-0">Manage your website configuration and company information</p>
                </div>
                <a href="<?php echo $baseUrl; ?>/admin/dashboard" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $baseUrl; ?>/admin/settings" id="settingsForm">
                <!-- General Settings -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h5 class="settings-card-title"><i class="fas fa-info-circle me-2 text-primary"></i>General Information</h5>
                    </div>
                    <div class="settings-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label">Company Name</label>
                                    <input type="text" name="site_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['general']['site_name']['setting_value'] ?? 'APS Dream Home'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label">Site Description</label>
                                    <input type="text" name="site_description" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['general']['site_description']['setting_value'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h5 class="settings-card-title"><i class="fas fa-phone me-2 text-success"></i>Contact Information</h5>
                    </div>
                    <div class="settings-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="contact_phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['general']['contact_phone']['setting_value'] ?? '+91 92771 21112'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label">WhatsApp Number</label>
                                    <input type="text" name="contact_whatsapp" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['general']['contact_whatsapp']['setting_value'] ?? '+91 92771 21112'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="contact_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['general']['contact_email']['setting_value'] ?? 'info@apsdreamhome.com'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label">Secondary Phone</label>
                                    <input type="text" name="contact_phone2" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['general']['contact_phone2']['setting_value'] ?? '+91 70074 44842'); ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="setting-item">
                                    <label class="form-label">Office Address</label>
                                    <textarea name="contact_address" class="form-control" rows="2"><?php echo htmlspecialchars($settings['general']['contact_address']['setting_value'] ?? '1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008'); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h5 class="settings-card-title"><i class="fas fa-share-alt me-2 text-info"></i>Social Media Links</h5>
                    </div>
                    <div class="settings-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label"><i class="fab fa-facebook me-2 text-primary"></i>Facebook</label>
                                    <input type="url" name="social_facebook" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['social']['social_facebook']['setting_value'] ?? 'https://www.facebook.com/apsdreamhomes/'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label"><i class="fab fa-instagram me-2 text-danger"></i>Instagram</label>
                                    <input type="url" name="social_instagram" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['social']['social_instagram']['setting_value'] ?? 'https://www.instagram.com/apsdreamhomes/'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label"><i class="fas fa-globe me-2 text-success"></i>JustDial</label>
                                    <input type="url" name="social_justdial" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['social']['social_justdial']['setting_value'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="setting-item">
                                    <label class="form-label"><i class="fas fa-briefcase me-2 text-warning"></i>FalconeBiz</label>
                                    <input type="url" name="social_falconebiz" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['social']['social_falconebiz']['setting_value'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Maps -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h5 class="settings-card-title"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Google Maps</h5>
                    </div>
                    <div class="settings-card-body">
                        <div class="setting-item">
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (alert.classList.contains('alert-success') || alert.classList.contains('alert-danger')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
</body>
</html>
