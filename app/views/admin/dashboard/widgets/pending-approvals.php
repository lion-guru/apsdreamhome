<?php
/**
 * Pending Approvals Widget
 */
$approvals = $widgetData['approvals'] ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="list-group list-group-flush">
    <?php if (empty($approvals)): ?>
        <div class="list-group-item text-center text-muted py-4">
            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
            <p class="mb-0">No pending approvals</p>
        </div>
    <?php else: ?>
        <?php foreach ($approvals as $approval): ?>
            <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1"><?php echo htmlspecialchars($approval['title'] ?? 'Approval Request'); ?></h6>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($approval['description'] ?? ''); ?>
                        </small>
                        <div class="text-muted small mt-1">
                            <?php echo date('M d, H:i', strtotime($approval['created_at'] ?? 'now')); ?>
                        </div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <a href="<?php echo $base . ($approval['url'] ?? '#'); ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-check me-1"></i> Approve
                        </a>
                        <a href="#" class="btn btn-sm btn-danger">Reject</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>