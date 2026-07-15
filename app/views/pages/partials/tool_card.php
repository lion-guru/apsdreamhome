<?php
/**
 * Tool Card Partial - Reusable tool card component
 * Expected variables: $tool (array with url, gradient, icon, title_key, title_default, desc_key, desc_default)
 */
$url = $tool['url'] ?? '#';
$gradient = $tool['gradient'] ?? 'linear-gradient(135deg, #0d9488, #0f766e)';
$icon = $tool['icon'] ?? 'fa-toolbox';
$title = __($tool['title_key'] ?? '', [], $tool['title_default'] ?? 'Tool');
$desc = __($tool['desc_key'] ?? '', [], $tool['desc_default'] ?? 'Description');
?>
<div class="col-lg-3 col-md-4 col-sm-6">
    <a href="<?php echo $url; ?>" class="text-decoration-none">
        <div class="tool-card h-100" style="background: #1e293b; border: 1px solid #334155; border-radius: 16px; overflow: hidden; transition: all 0.3s;">
            <div style="background: <?php echo $gradient; ?>; height: 4px;"></div>
            <div class="p-4">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: <?php echo $gradient; ?>; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <i class="fas <?php echo $icon; ?>" style="font-size: 22px; color: #fff;"></i>
                </div>
                <h5 class="text-white fw-semibold mb-2" style="font-size: 1rem;"><?php echo $title; ?></h5>
                <p class="text-white-50 small mb-0" style="line-height: 1.5;"><?php echo $desc; ?></p>
            </div>
            <div style="padding: 0 20px 20px;">
                <span class="btn-sm" style="background: rgba(255,255,255,0.05); border: 1px solid #334155; color: #94a3b8; padding: 8px 16px; border-radius: 8px; font-size: 0.75rem; font-weight: 500;">
                    <i class="fas fa-arrow-right me-1"></i><?php echo __('try_now', [], 'Try Now'); ?>
                </span>
            </div>
        </div>
    </a>
</div>