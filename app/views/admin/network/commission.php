<?php
/**
 * Commission Structure View
 */
$commission_levels = $commission_levels ?? [];
$rank_requirements = $rank_requirements ?? [];
$payout_history = $payout_history ?? [];
$page_title = $page_title ?? 'Commission Structure';
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
                <h2 class="mb-1">Commission Structure</h2>
                <p class="text-muted mb-0">MLM commission levels and payouts</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/network" class="btn btn-outline-secondary me-2">Overview</a>
                <a href="<?php echo $base; ?>/admin/network/tree" class="btn btn-outline-primary">Tree View</a>
            </div>
        </div>
        
        <!-- Commission Levels -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Commission Levels</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($commission_levels)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Name</th>
                                    <th>Commission %</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($commission_levels as $level): ?>
                                    <tr>
                                        <td><?php echo $level['level'] ?? '-'; ?></td>
                                        <td><?php echo htmlspecialchars($level['name'] ?? '-'); ?></td>
                                        <td><?php echo $level['commission_percentage'] ?? '0'; ?>%</td>
                                        <td><?php echo htmlspecialchars($level['description'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Commission levels will be configured here.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Rank Requirements -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Rank Requirements</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($rank_requirements)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($rank_requirements as $rank): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($rank['rank_name'] ?? '-'); ?></h6>
                                            <small class="text-muted">
                                                Min: <?php echo $rank['min_referrals'] ?? 0; ?> referrals, 
                                                ₹<?php echo number_format(floatval(rank['min_sales'] ?? 0) ?? 0); ?> sales
                                            </small>
                                        </div>
                                        <span class="badge bg-primary">Level <?php echo $rank['rank_order'] ?? 0; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Rank requirements will be configured here.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Payouts</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($payout_history)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($payout_history as $payout): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($payout['associate_name'] ?? '-'); ?></h6>
                                            <span class="text-success fw-bold">₹<?php echo number_format(floatval(payout['amount'] ?? 0) ?? 0); ?></span>
                                        </div>
                                        <small class="text-muted"><?php echo isset($payout['payout_date']) ? date('M d, Y', strtotime($payout['payout_date'])) : '-'; ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                No recent payouts to display.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
