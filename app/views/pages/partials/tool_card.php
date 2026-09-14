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
$toolSlug = basename($url); // e.g., 'calc', 'stamp-duty-calculator', etc.
?>
<div class="col-lg-3 col-md-4 col-sm-6">
    <div class="tool-card h-100 p-4 text-white text-decoration-none" style="background: <?= $gradient ?>; border-radius: 16px; transition: all 0.3s ease; cursor: pointer;" 
         onclick="openToolModal('<?= $toolSlug ?>')" role="button" tabindex="0" aria-label="<?= $title ?>">
        <div style="height: 4px; width: 100%; background: rgba(255,255,255,0.3); border-radius: 8px 8px 0 0; margin: -1rem -1rem 1rem -1rem;"></div>
        <div class="p-4 h-100 d-flex flex-column">
            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i class="fas <?= $icon ?> fa-2x text-white"></i>
            </div>
            <h5 class="fw-semibold mb-2 text-white"><?= $title ?></h5>
            <p class="small mb-0 flex-grow-1" style="opacity: 0.8;"><?= $desc ?></p>
            <div class="mt-auto pt-3">
                <span class="btn-sm" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 8px 16px; border-radius: 8px; font-size: 0.75rem; font-weight: 500;">
                    <i class="fas fa-arrow-right me-1"></i><?= __('try_now', [], 'Try Now') ?>
                </span>
            </div>
        </div>
    </div>
</div>