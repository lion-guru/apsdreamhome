<?php
/**
 * Payment Analytics View
 */
$analytics_data = $analytics_data ?? [];
$page_title = $page_title ?? 'Payment Analytics';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Payment Analytics</h2>
                <p class="text-muted mb-0">Payment trends and statistics</p>
            </div>
            <a href="<?php echo $base; ?>/admin/payments" class="btn btn-outline-secondary">Back to Payments</a>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Payment Trends (30 Days)</h5>
                    </div>
                    <div class="card-body">
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
                                                <td><?php echo $trend['count']; ?></td>
                                                <td>₹<?php echo number_format(floatval(trend['total'] ?? 0)); ?></td>
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
                    <div class="card-body">
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
                                                <td><?php echo $method['count']; ?></td>
                                                <td>₹<?php echo number_format(floatval(method['total'] ?? 0)); ?></td>
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
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Top Customers</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($analytics_data['top_customers'])): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($analytics_data['top_customers'], 0, 5) as $customer): ?>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span><?php echo htmlspecialchars(customer['name'] ?? ''); ?></span>
                                        <span class="fw-bold">₹<?php echo number_format(floatval(customer['total_paid'] ?? 0)); ?></span>
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
                    <div class="card-body">
                        <?php if (!empty($analytics_data['status_distribution'])): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($analytics_data['status_distribution'] as $status): ?>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span><?php echo ucfirst($status['status']); ?></span>
                                        <span class="badge bg-primary"><?php echo $status['count']; ?></span>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
