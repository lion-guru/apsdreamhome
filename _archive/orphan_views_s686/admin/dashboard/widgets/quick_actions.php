<?php
/**
 * Quick Actions Widget Partial
 * Expected $widget config: title, actions (array of {icon, color, label, url})
 */
$config = $widget['config'] ?? [];
$title = $config['title'] ?? '';
$actions = $config['actions'] ?? [];
$widgetId = $widget['id'] ?? 'actions_' . uniqid();
?>

<div class="grid-stack-item" data-gs-id="<?= htmlspecialchars($widgetId) ?>" data-gs-x="<?= (int)($widget['x'] ?? 0) ?>" data-gs-y="<?= (int)($widget['y'] ?? 0) ?>" data-gs-w="<?= (int)($widget['w'] ?? 12) ?>" data-gs-h="<?= (int)($widget['h'] ?? 6) ?>">
    <div class="widget-card actions-card">
        <div class="widget-header">
            <h6 class="widget-title"><?= htmlspecialchars($title) ?></h6>
            <div class="widget-actions">
                <button type="button" class="btn btn-sm btn-outline-secondary widget-config" data-widget-id="<?= htmlspecialchars($widgetId) ?>" title="Configure">
                    <i class="fas fa-cog"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger widget-remove" data-widget-id="<?= htmlspecialchars($widgetId) ?>" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="widget-body">
            <div class="actions-grid">
                <?php foreach ($actions as $action): ?>
                <a href="<?= htmlspecialchars($action['url'] ?? '#') ?>" class="action-btn" style="--action-color: <?= htmlspecialchars($action['color'] ?? '#3b82f6') ?>">
                    <i class="<?= htmlspecialchars($action['icon'] ?? 'fas fa-link') ?> fa-lg"></i>
                    <span><?= htmlspecialchars($action['label'] ?? '') ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>