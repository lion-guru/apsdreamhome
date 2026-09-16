<?php
/**
 * Quick Actions Widget
 */
$actions = $widgetData['actions'] ?? [
    ['label' => 'New Booking', 'icon' => 'plus', 'url' => '/admin/booking/create', 'color' => 'primary'],
    ['label' => 'New Lead', 'icon' => 'user-plus', 'url' => '/admin/leads/create', 'color' => 'success'],
    ['label' => 'New Property', 'icon' => 'home', 'url' => '/admin/properties/create', 'color' => 'info'],
    ['label' => 'New Lead', 'icon' => 'user-plus', 'url' => '/associate/leads/add', 'color' => 'success'],
];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="row g-2">
    <?php foreach ($actions as $action): ?>
        <div class="col-6 col-md-3">
            <a href="<?php echo $base . $action['url']; ?>" class="btn btn-outline-<?php echo $action['color']; ?> w-100 py-3">
                <i class="fas fa-<?php echo $action['icon']; ?> fa-lg mb-1 d-block"></i>
                <span><?php echo $action['label']; ?></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>