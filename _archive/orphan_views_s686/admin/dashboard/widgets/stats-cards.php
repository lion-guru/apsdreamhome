<?php
/**
 * Stats Cards Widget
 */
$widgetData = $widgetData ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="row g-3">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Total Users</h6>
                        <h2 class="mb-0"><?php echo number_format($widgetData['total_users'] ?? 0); ?></h2>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Total Bookings</h6>
                        <h2 class="mb-0"><?php echo number_format($widgetData['total_bookings'] ?? 0); ?></h2>
                    </div>
                    <i class="fas fa-home fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Total Revenue</h6>
                        <h2 class="mb-0">₹<?php echo number_format($widgetData['total_revenue'] ?? 0, 0); ?></h2>
                    </div>
                    <i class="fas fa-rupee-sign fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-white-50">Pending Payments</h6>
                        <h2 class="mb-0"><?php echo number_format($widgetData['pending_payments'] ?? 0); ?></h2>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>