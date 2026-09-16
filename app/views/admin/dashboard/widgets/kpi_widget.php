<?php
/**
 * KPI Widget Partial
 * Expected $widget config: title, value, target, unit, icon, color, format
 */
$config = $widget['config'] ?? [];
$title = $config['title'] ?? '';
$value = $config['value'] ?? 0;
$target = $config['target'] ?? 0;
$unit = $config['unit'] ?? '';
$icon = $config['icon'] ?? 'fas fa-bullseye';
$color = $config['color'] ?? '#10b981';
$format = $config['format'] ?? 'number';
$widgetId = $widget['id'] ?? 'kpi_' . uniqid();

$progress = $target > 0 ? min(100, round(($value / $target) * 100)) : 0;
$formattedValue = match($format) {
    'percentage' => number_format($value, 1) . '%',
    'currency' => '₹' . number_format($value, 0),
    default => is_numeric($value) ? number_format($value) : htmlspecialchars($value)
};
$formattedTarget = match($format) {
    'percentage' => number_format($target, 1) . '%',
    'currency' => '₹' . number_format($target, 0),
    default => is_numeric($target) ? number_format($target) : htmlspecialchars($target)
};
?>

<div class="grid-stack-item" data-gs-id="<?= htmlspecialchars($widgetId) ?>" data-gs-x="<?= (int)($widget['x'] ?? 0) ?>" data-gs-y="<?= (int)($widget['y'] ?? 0) ?>" data-gs-w="<?= (int)($widget['w'] ?? 4) ?>" data-gs-h="<?= (int)($widget['h'] ?? 8) ?>">
    <div class="widget-card kpi-card" style="--kpi-color: <?= htmlspecialchars($color) ?>">
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
            <div class="kpi-content">
                <div class="kpi-icon" style="background: <?= htmlspecialchars($color) ?>15; color: <?= htmlspecialchars($color) ?>">
                    <i class="<?= htmlspecialchars($icon) ?> fa-2x"></i>
                </div>
                <div class="kpi-value"><?= $formattedValue ?></div>
                <div class="kpi-unit"><?= htmlspecialchars($unit) ?></div>
                
                <?php if ($target > 0): ?>
                <div class="kpi-progress">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: <?= $progress ?>%; background: <?= htmlspecialchars($color) ?>"
                             aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="kpi-target text-muted small">
                        Target: <?= $formattedTarget ?> (<?= $progress ?>%)
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>