<?php
/**
 * Engagement Dashboard View
 */
$engagement_data = $engagement_data ?? [];
$page_title = $page_title ?? 'Engagement';
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
                <h2 class="mb-1"><i class="fas fa-heart me-2 text-danger"></i>User Engagement</h2>
                <p class="text-muted mb-0">Track user interactions and activities</p>
            </div>
            <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back</a>
        </div>
        
        <!-- Engagement Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-eye fa-2x text-primary mb-2"></i>
                        <h4><?php echo number_format(floatval(engagement_data['total_views'] ?? 0) ?? 0); ?></h4>
                        <p class="text-muted mb-0">Total Views</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-heart fa-2x text-danger mb-2"></i>
                        <h4><?php echo number_format(floatval(engagement_data['favorites'] ?? 0) ?? 0); ?></h4>
                        <p class="text-muted mb-0">Favorites</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-share fa-2x text-info mb-2"></i>
                        <h4><?php echo number_format(floatval(engagement_data['shares'] ?? 0) ?? 0); ?></h4>
                        <p class="text-muted mb-0">Shares</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-comment fa-2x text-success mb-2"></i>
                        <h4><?php echo number_format(floatval(engagement_data['reviews'] ?? 0) ?? 0); ?></h4>
                        <p class="text-muted mb-0">Reviews</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($engagement_data['activities'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($engagement_data['activities'] as $activity): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-<?php echo $activity['icon'] ?? 'circle'; ?> me-2 text-<?php echo $activity['color'] ?? 'primary'; ?>"></i>
                                    <?php echo htmlspecialchars(activity['description'] ?? ''); ?>
                                </div>
                                <small class="text-muted"><?php echo $activity['time']; ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent activities</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
