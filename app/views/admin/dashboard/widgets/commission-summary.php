<?php
/**
 * Commission Summary Widget
 */
$data = $widgetData ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="row g-3 text-center">
    <div class="col-6">
        <div class="card bg-success bg-opacity-10 border-success h-100">
            <div class="card-body py-3">
                <h6 class="text-success mb-1">This Month</h6>
                <h3 class="mb-0 text-success">₹<?php echo number_format($data['this_month'] ?? 0, 0); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card bg-primary bg-opacity-10 border-primary h-100">
            <div class="card-body py-3">
                <h6 class="text-primary mb-1">Total Earned</h6>
                <h3 class="mb-0 text-primary">₹<?php echo number_format($data['total_earned'] ?? 0, 0); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card bg-warning bg-opacity-10 border-warning h-100">
            <div class="card-body py-3">
                <h6 class="text-warning mb-1">Pending</h6>
                <h3 class="mb-0 text-warning">₹<?php echo number_format($data['pending'] ?? 0, 0); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card bg-info bg-opacity-10 border-info h-100">
            <div class="card-body py-3">
                <h6 class="text-info mb-1">This Week</h6>
                <h3 class="mb-0 text-info">₹<?php echo number_format($data['this_week'] ?? 0, 0); ?></h3>
            </div>
        </div>
    </div>
</div>
<a href="<?php echo $base; ?>/associate/commission" class="btn btn-sm btn-outline-primary w-100 mt-3">
    <i class="fas fa-chart-line me-1"></i> View Details
</a>