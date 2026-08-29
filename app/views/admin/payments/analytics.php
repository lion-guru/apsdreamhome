<?php
$analytics_data = $analytics_data ?? [];
$page_title = $page_title ?? 'Payment Analytics';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Payment Analytics</h2>
                <p class="text-muted mb-0">Payment trends and statistics</p>
            </div>
            <a href="<?php echo e($base); ?>/admin/payments" class="btn btn-outline-secondary">Back to Payments</a>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Payment Trends (30 Days)</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (!empty($analytics_data['trends'])): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr><th>Date</th><th>Count</th><th>Amount</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analytics_data['trends'] as $trend): ?>
                                            <tr>
                                                <td><?php echo date('M d', strtotime($trend['date'])); ?></td>
                                                <td><?php echo e($trend['count']); ?></td>
                                                <td>₹<?php echo number_format(floatval($trend['total'] ?? 0)); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No trend data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Methods</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (!empty($analytics_data['methods'])): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr><th>Method</th><th>Count</th><th>Total</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analytics_data['methods'] as $method): ?>
                                            <tr>
                                                <td><?php echo ucfirst($method['payment_method']); ?></td>
                                                <td><?php echo e($method['count']); ?></td>
                                                <td>₹<?php echo number_format(floatval($method['total'] ?? 0)); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No method data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Top users</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (!empty($analytics_data['top_customers'])): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($analytics_data['top_customers'], 0, 5) as $customer): ?>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span><?php echo htmlspecialchars($customer['name'] ?? ''); ?></span>
                                        <span class="fw-bold">₹<?php echo number_format(floatval($customer['total_paid'] ?? 0)); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No customer data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status Distribution</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (!empty($analytics_data['status_distribution'])): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($analytics_data['status_distribution'] as $status): ?>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span><?php echo ucfirst($status['status']); ?></span>
                                        <span class="badge bg-primary"><?php echo e($status['count']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No status data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

