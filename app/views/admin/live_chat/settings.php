<?php
$page_title = $page_title ?? 'Widget Settings';
$page_heading = $page_heading ?? 'Live Chat Widget Settings';
$content = $content ?? '';
$settings = $settings ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Widget Settings</h2>
        <a href="<?= BASE_URL ?>/admin/live-chat" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/admin/live-chat/settings">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Widget Title</label>
                        <input type="text" name="widget_title" class="form-control" value="<?= htmlspecialchars($settings['widget_title'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Subtitle</label>
                        <input type="text" name="widget_subtitle" class="form-control" value="<?= htmlspecialchars($settings['widget_subtitle'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <select name="widget_position" class="form-select">
                            <option value="bottom-right" <?= ($settings['widget_position'] ?? '') === 'bottom-right' ? 'selected' : '' ?>>Bottom Right</option>
                            <option value="bottom-left" <?= ($settings['widget_position'] ?? '') === 'bottom-left' ? 'selected' : '' ?>>Bottom Left</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Theme Color</label>
                        <input type="color" name="widget_color" class="form-control form-control-color" value="<?= htmlspecialchars($settings['widget_color'] ?? '#007bff') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="widget_enabled" class="form-select">
                            <option value="1" <?= ($settings['widget_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= ($settings['widget_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Welcome Message</label>
                        <textarea name="welcome_message" class="form-control" rows="3"><?= htmlspecialchars($settings['welcome_message'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Offline Message</label>
                        <textarea name="offline_message" class="form-control" rows="3"><?= htmlspecialchars($settings['offline_message'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Business Hours Only</label>
                        <select name="business_hours_only" class="form-select">
                            <option value="0" <?= ($settings['business_hours_only'] ?? '0') === '0' ? 'selected' : '' ?>>No (24/7)</option>
                            <option value="1" <?= ($settings['business_hours_only'] ?? '0') === '1' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="business_hours_start" class="form-control" value="<?= htmlspecialchars($settings['business_hours_start'] ?? '09:00') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Time</label>
                        <input type="time" name="business_hours_end" class="form-control" value="<?= htmlspecialchars($settings['business_hours_end'] ?? '19:00') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Auto-Assign</label>
                        <select name="auto_assign" class="form-select">
                            <option value="1" <?= ($settings['auto_assign'] ?? '1') === '1' ? 'selected' : '' ?>>Yes (auto-assign to available agent)</option>
                            <option value="0" <?= ($settings['auto_assign'] ?? '1') === '0' ? 'selected' : '' ?>>No (manual)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Settings</button>
            </div>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
