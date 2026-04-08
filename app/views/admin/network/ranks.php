<?php
/**
 * Rank Management View
 */
$ranks = $ranks ?? [];
$rank_distribution = $rank_distribution ?? [];
$rank_progression = $rank_progression ?? [];
$page_title = $page_title ?? 'Rank Management';
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
                <h2 class="mb-1">Rank Management</h2>
                <p class="text-muted mb-0">MLM rank structure and distribution</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/network" class="btn btn-outline-secondary me-2">Overview</a>
                <a href="<?php echo $base; ?>/admin/network/commission" class="btn btn-outline-primary">Commission</a>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-crown fa-2x text-warning mb-2"></i>
                        <h3 class="mb-1"><?php echo count($ranks); ?></h3>
                        <p class="text-muted mb-0">Total Ranks</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h3 class="mb-1"><?php echo count($rank_distribution); ?></h3>
                        <p class="text-muted mb-0">Active Ranks</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                        <h3 class="mb-1"><?php echo count($rank_progression); ?></h3>
                        <p class="text-muted mb-0">Progress Tracking</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Rank Distribution -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Rank Distribution</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($rank_distribution)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($rank_distribution as $dist): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold"><?php echo htmlspecialchars($dist['mlm_rank'] ?? 'Unranked'); ?></span>
                                        <span class="badge bg-primary"><?php echo $dist['count'] ?? 0; ?> associates</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Rank distribution data will appear here.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Available Ranks</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ranks)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Order</th>
                                            <th>Benefits</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ranks as $rank): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($rank['rank_name'] ?? '-'); ?></td>
                                                <td><?php echo $rank['rank_order'] ?? '-'; ?></td>
                                                <td><?php echo htmlspecialchars($rank['benefits'] ?? '-'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Rank configuration will appear here.
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
