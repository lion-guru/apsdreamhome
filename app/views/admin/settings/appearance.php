<?php
$s = $settings ?? [];
$nav = json_encode(json_decode($s['nav_json'] ?? '[]', true), JSON_PRETTY_PRINT);
$social = json_encode(json_decode($s['social_json'] ?? '[]', true), JSON_PRETTY_PRINT);
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <h3 class="mb-4">Appearance Settings</h3>
            <form method="post" action="<?php echo BASE_URL; ?>/admin/settings/appearance">
                <div class="mb-3">
                    <label class="form-label">Brand Name</label>
                    <input type="text" name="brand_name" class="form-control" value="<?php echo h($s['brand_name'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Logo URL</label>
                    <input type="text" name="logo_url" class="form-control" value="<?php echo h($s['logo_url'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Favicon URL</label>
                    <input type="text" name="favicon_url" class="form-control" value="<?php echo h($s['favicon_url'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Navigation (JSON)</label>
                    <textarea name="nav_json" class="form-control" rows="8"><?php echo h($nav); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Social Links (JSON)</label>
                    <textarea name="social_json" class="form-control" rows="6"><?php echo h($social); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Footer HTML</label>
                    <textarea name="footer_html" class="form-control" rows="6"><?php echo h($s['footer_html'] ?? ''); ?></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
