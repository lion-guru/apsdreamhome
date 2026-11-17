<?php
/**
 * Recent Bookings Widget
 */
$bookings = $widgetData['bookings'] ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="list-group list-group-flush">
    <?php if (empty($bookings)): ?>
        <div class="list-group-item text-center text-muted py-4">
            <i class="fas fa-home fa-2x mb-2"></i>
            <p class="mb-0">No recent bookings</p>
        </div>
    <?php else: ?>
        <?php foreach ($bookings as $booking): ?>
            <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">Plot <?php echo htmlspecialchars($booking['plot_number'] ?? $booking['id']); ?></h6>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($booking['customer_name'] ?? 'Unknown'); ?>
                            • <?php echo htmlspecialchars($booking['colony_name'] ?? ''); ?>
                        </small>
                        <div class="text-muted small mt-1">
                            <?php echo date('M d, Y', strtotime($booking['booking_date'] ?? $booking['created_at'] ?? 'now')); ?>
                        </div>
                    </div>
                    <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                        <?php echo ucfirst($booking['status'] ?? 'pending'); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>