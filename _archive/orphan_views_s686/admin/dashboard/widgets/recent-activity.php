<?php
/**
 * Recent Activity Widget
 */
$activities = $widgetData['activities'] ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="list-group list-group-flush">
    <?php if (empty($activities)): ?>
        <div class="list-group-item text-center text-muted py-4">
            <i class="fas fa-history fa-2x mb-2"></i>
            <p class="mb-0">No recent activity</p>
        </div>
    <?php else: ?>
        <?php foreach ($activities as $activity): ?>
            <div class="list-group-item px-0">
                <div class="d-flex align-items-start">
                    <div class="activity-icon me-3">
                        <i class="fas fa-<?php echo $activity['icon'] ?? 'circle'; ?> text-<?php echo $activity['color'] ?? 'primary'; ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['title'] ?? 'Activity'); ?></h6>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($activity['description'] ?? ''); ?>
                        </small>
                        <div class="text-muted small mt-1">
                            <?php echo date('M d, H:i', strtotime($activity['created_at'] ?? 'now')); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>