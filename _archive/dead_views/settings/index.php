<?php
$settings = $settings ?? [];
$page_title = $page_title ?? 'Site Settings';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Site Settings</h2>
                <p class="text-muted mb-0">Manage website configuration</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/settings/edit" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-2"></i>Edit Settings
                </a>
                <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
        
        <!-- Settings Cards -->
        <div class="row">
            <?php foreach ($settings as $category => $categorySettings): ?>
                <?php if (!empty($categorySettings)): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-<?php echo $category === 'general' ? 'cog' : ($category === 'appearance' ? 'paint-brush' : ($category === 'email' ? 'envelope' : ($category === 'social' ? 'share-alt' : 'search'))); ?> me-2"></i>
                                <?php echo ucfirst($category); ?> Settings
                            </h5>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <?php foreach ($categorySettings as $key => $setting): ?>
                                            <tr>
                                                <td class="text-muted" style="width: 40%;"><?php echo htmlspecialchars(str_replace('_', ' ', $key)); ?></td>
                                                <td class="fw-semibold">
                                                    <?php 
                                                        $value = $setting['setting_value'] ?? '';
                                                        if (strlen($value) > 50) {
                                                            echo htmlspecialchars(substr($value, 0, 50)) . '...';
                                                        } else {
                                                            echo htmlspecialchars($value);
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($settings['general']) && empty($settings['appearance']) && empty($settings['email']) && empty($settings['social']) && empty($settings['seo'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No settings configured yet. Click "Edit Settings" to add configuration.
        </div>
        <?php endif; ?>
    </div>
    

