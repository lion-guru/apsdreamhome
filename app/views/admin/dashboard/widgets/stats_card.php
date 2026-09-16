<?php
/**
 * Stats Card Widget Partial
 * Expected $widget config: title, value, icon, color, trend, trend_label
 */
$config = $widget['config'] ?? [];
$title = $config['title'] ?? '';
$value = $config['value'] ?? 0;
$icon = $config['icon'] ?? 'fas fa-chart-bar';
$color = $config['color'] ?? '#3b82f6';
$trend = $config['trend'] ?? 0;
$trendLabel = $config['trend_label'] ?? '';
$widgetId = $widget['id'] ?? 'stats_' . uniqid();
?>

<div class="grid-stack-item" data-gs-id="<?= htmlspecialchars($widgetId) ?>" data-gs-x="<?= (int)($widget['x'] ?? 0) ?>" data-gs-y="<?= (int)($widget['y'] ?? 0) ?>" data-gs-w="<?= (int)($widget['w'] ?? 3) ?>" data-gs-h="<?= (int)($widget['h'] ?? 4) ?>">
    <div class="widget-card stats-card" style="--widget-color: <?= htmlspecialchars($color) ?>">
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
            <div class="stats-content">
                <div class="stats-icon" style="background: <?= htmlspecialchars($color) ?>15; color: <?= htmlspecialchars($color) ?>">
                    <i class="<?= htmlspecialchars($icon) ?> fa-2x"></i>
                </div>
                <div class="stats-value"><?= is_numeric($value) ? number_format($value) : htmlspecialchars($value) ?></div>
                <?php if ($trend !== 0): ?>
                <div class="stats-trend <?= $trend > 0 ? 'positive' : 'negative' ?>">
                    <i class="fas fa-arrow-<?= $trend > 0 ? 'up' : 'down' ?>"></i>
                    <span><?= abs($trend) ?>%</span>
                    <?php if ($trendLabel): ?>
                    <small><?= htmlspecialchars($trendLabel) ?></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>