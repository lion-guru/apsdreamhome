<?php
/**
 * Analytics Dashboard View
 */
$analytics_data = $analytics_data ?? [];
$charts = $charts ?? [];
$page_title = $page_title ?? 'Analytics Dashboard';
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
                <h2 class="mb-1"><i class="fas fa-chart-line me-2 text-primary"></i>Analytics Dashboard</h2>
                <p class="text-muted mb-0">Real-time business insights and metrics</p>
            </div>
            <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
        
        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Revenue</p>
                                <h4 class="mb-0">₹<?php echo number_format(floatval(analytics_data['total_revenue'] ?? 0) ?? 0); ?></h4>
                            </div>
                            <div class="bg-success bg-opacity-10 rounded p-2">
                                <i class="fas fa-rupee-sign text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Active Users</p>
                                <h4 class="mb-0"><?php echo number_format(floatval(analytics_data['active_users'] ?? 0) ?? 0); ?></h4>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded p-2">
                                <i class="fas fa-users text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">New Leads</p>
                                <h4 class="mb-0"><?php echo number_format(floatval(analytics_data['new_leads'] ?? 0) ?? 0); ?></h4>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded p-2">
                                <i class="fas fa-bullseye text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Conversion Rate</p>
                                <h4 class="mb-0"><?php echo $analytics_data['conversion_rate'] ?? 0; ?>%</h4>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded p-2">
                                <i class="fas fa-percentage text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Revenue Trend (30 Days)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($charts['revenue'])): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead><tr><th>Date</th><th>Revenue</th><th>Bookings</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($charts['revenue'] as $item): ?>
                                            <tr>
                                                <td><?php echo date('M d', strtotime($item['date'])); ?></td>
                                                <td>₹<?php echo number_format(floatval(item['amount'] ?? 0)); ?></td>
                                                <td><?php echo $item['count']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No revenue data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>User Growth</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($charts['users'])): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($charts['users'] as $user): ?>
                                    <div class="list-group-item d-flex justify-content-between">
                                        <span><?php echo $user['type']; ?></span>
                                        <span class="badge bg-primary"><?php echo $user['count']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No user data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Property Views -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Top Performing Properties</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($analytics_data['top_properties'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Property</th><th>Views</th><th>Inquiries</th><th>Conversion</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analytics_data['top_properties'] as $prop): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(prop['title'] ?? ''); ?></td>
                                        <td><?php echo number_format(floatval(prop['views'] ?? 0)); ?></td>
                                        <td><?php echo number_format(floatval(prop['inquiries'] ?? 0)); ?></td>
                                        <td><?php echo $prop['conversion']; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No property data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
