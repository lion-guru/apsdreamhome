<?php
/**
 * Admin Reports View
 * Analytics and reporting dashboard
 */
$reports = $reports ?? [];
$page_title = $page_title ?? 'Reports & Analytics';
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
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Reports & Analytics</h2>
                <p class="text-muted mb-0">View system performance and statistics</p>
            </div>
            <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
        
        <!-- Report Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users me-2 text-primary"></i>User Registrations</h5>
                        <p class="text-muted">Monthly user registration trends</p>
                        <div class="mt-3">
                            <strong>Total:</strong> <?php echo array_sum($reports['user_registrations']['data'] ?? [0]); ?> users
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-eye me-2 text-success"></i>Property Views</h5>
                        <p class="text-muted">Property listing view statistics</p>
                        <div class="mt-3">
                            <strong>Total:</strong> <?php echo array_sum($reports['property_views']['data'] ?? [0]); ?> views
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-rupee-sign me-2 text-info"></i>Revenue</h5>
                        <p class="text-muted">Monthly revenue analytics</p>
                        <div class="mt-3">
                            <strong>Total:</strong> ₹<?php echo number_format(array_sum($reports['revenue']['data'] ?? [0])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Analytics Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-center">User Growth</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Users</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                $labels = $reports['user_registrations']['labels'] ?? [];
                                                $data = $reports['user_registrations']['data'] ?? [];
                                                foreach ($labels as $i => $label): 
                                            ?>
                                                <tr>
                                                    <td><?php echo $label; ?></td>
                                                    <td><?php echo $data[$i] ?? 0; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-center">Property Views</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                $labels = $reports['property_views']['labels'] ?? [];
                                                $data = $reports['property_views']['data'] ?? [];
                                                foreach ($labels as $i => $label): 
                                            ?>
                                                <tr>
                                                    <td><?php echo $label; ?></td>
                                                    <td><?php echo $data[$i] ?? 0; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-center">Revenue</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                $labels = $reports['revenue']['labels'] ?? [];
                                                $data = $reports['revenue']['data'] ?? [];
                                                foreach ($labels as $i => $label): 
                                            ?>
                                                <tr>
                                                    <td><?php echo $label; ?></td>
                                                    <td>₹<?php echo number_format($data[$i] ?? 0); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
