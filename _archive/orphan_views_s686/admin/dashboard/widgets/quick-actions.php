<?php
/**
 * Quick Actions Widget
 */
$actions = $widgetData['actions'] ?? [
    ['label' => 'New Booking', 'icon' => 'plus', 'url' => '/admin/booking/create', 'color' => 'primary'],
    ['label' => 'New Lead', 'icon' => 'user-plus', 'url' => '/admin/leads/create', 'color' => 'success'],
    ['label' => 'New Property', 'icon' => 'home', 'url' => '/admin/properties/create', 'color' => 'info'],
    ['label' => 'Add Colony', 'icon' => 'city', 'url' => '/admin/colonies/create', 'color' => 'warning'],
];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="row g-2">
    <?php foreach ($actions as $action): ?>
        <div class="col-6 col-md-3">
            <a href="<?php echo htmlspecialchars($base . $action['url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-<?php echo htmlspecialchars($action['color'] ?? 'primary', ENT_QUOTES, 'UTF-8'); ?> w-100 py-3">
                <i class="fas fa-<?php echo htmlspecialchars($action['icon'] ?? 'circle', ENT_QUOTES, 'UTF-8'); ?> fa-lg mb-1 d-block"></i>
                <span><?php echo htmlspecialchars($action['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>