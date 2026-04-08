<?php
/**
 * Sales Dashboard View
 */
$sales_data = $sales_data ?? [];
$performance = $performance ?? [];
$page_title = $page_title ?? 'Sales Dashboard';
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
                <h2 class="mb-1"><i class="fas fa-chart-bar me-2 text-primary"></i>Sales Dashboard</h2>
                <p class="text-muted mb-0">Track sales performance and targets</p>
            </div>
            <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back</a>
        </div>
        
        <!-- Sales Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Today's Sales</p>
                                <h4 class="mb-0">₹<?php echo number_format($sales_data['today_sales'] ?? 0); ?></h4>
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
                                <p class="text-muted mb-1">This Month</p>
                                <h4 class="mb-0">₹<?php echo number_format($sales_data['month_sales'] ?? 0); ?></h4>
                            </div>
                            <div class="bg-primary bg-opacity-10 rounded p-2">
                                <i class="fas fa-calendar text-primary"></i>
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
                                <p class="text-muted mb-1">Total Bookings</p>
                                <h4 class="mb-0"><?php echo number_format($sales_data['total_bookings'] ?? 0); ?></h4>
                            </div>
                            <div class="bg-info bg-opacity-10 rounded p-2">
                                <i class="fas fa-file-contract text-info"></i>
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
                                <p class="text-muted mb-1">Avg Deal Size</p>
                                <h4 class="mb-0">₹<?php echo number_format($sales_data['avg_deal'] ?? 0); ?></h4>
                            </div>
                            <div class="bg-warning bg-opacity-10 rounded p-2">
                                <i class="fas fa-chart-line text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Performers -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Top Sales Performers</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($performance)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Rank</th><th>Sales Person</th><th>Bookings</th><th>Revenue</th><th>Conversion</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($performance as $index => $performer): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index < 3): ?>
                                                <i class="fas fa-medal text-<?php echo ['gold', 'silver', 'bronze'][$index] ?? 'muted'; ?>"></i>
                                            <?php else: ?>
                                                <?php echo $index + 1; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($performer['name']); ?></td>
                                        <td><?php echo $performer['bookings']; ?></td>
                                        <td>₹<?php echo number_format($performer['revenue']); ?></td>
                                        <td><?php echo $performer['conversion']; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No performance data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
