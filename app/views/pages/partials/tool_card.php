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
        <div class="tool-card h-100" class="style-59147">
            <div class="style-86397"></div>
            <div class="p-4">
                <div class="style-99166">
                    <i class="fas <?php echo $icon; ?>" class="style-65735"></i>
                </div>
                <h5 class="fw-semibold mb-2"><?php echo $title; ?></h5>
                <p class="small mb-0" style="opacity: 0.7;"><?php echo $desc; ?></p>
            </div>
            <div class="style-16016">
                <span class="btn-sm" class="style-93887">
                    <i class="fas fa-arrow-right me-1"></i><?php echo __('try_now', [], 'Try Now'); ?>
                </span>
            </div>
        </div>
    </a>
</div>