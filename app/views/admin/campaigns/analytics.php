<?php
/**
 * Campaign Analytics View
 */
$campaign = $campaign ?? [];
$page_title = $page_title ?? 'Campaign Analytics';
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
                <h2 class="mb-1">Campaign Analytics</h2>
                <p class="text-muted mb-0">Performance metrics and insights</p>
            </div>
            <div>
                <a href="<?php echo $base; ?>/admin/campaigns/<?php echo $campaign['campaign_id'] ?? ''; ?>/edit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-edit me-2"></i>Edit Campaign
                </a>
                <a href="<?php echo $base; ?>/admin/campaigns" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
        
        <?php if (!empty($campaign)): ?>
        <!-- Campaign Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-1"><?php echo htmlspecialchars($campaign['name'] ?? 'Unknown'); ?></h4>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($campaign['description'] ?? ''); ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="badge bg-<?php echo ($campaign['status'] ?? '') === 'active' ? 'success' : (($campaign['status'] ?? '') === 'planned' ? 'warning' : 'secondary'); ?> me-2">
                            <?php echo ucfirst($campaign['status'] ?? 'unknown'); ?>
                        </span>
                        <span class="badge bg-info">
                            <?php echo ucfirst($campaign['type'] ?? 'general'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-eye fa-2x text-primary mb-2"></i>
                        <h3 class="mb-1">0</h3>
                        <p class="text-muted mb-0">Total Views</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-mouse-pointer fa-2x text-success mb-2"></i>
                        <h3 class="mb-1">0</h3>
                        <p class="text-muted mb-0">Clicks</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-percentage fa-2x text-warning mb-2"></i>
                        <h3 class="mb-1">0%</h3>
                        <p class="text-muted mb-0">Conversion Rate</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-rupee-sign fa-2x text-info mb-2"></i>
                        <h3 class="mb-1">₹<?php echo number_format($campaign['budget'] ?? 0); ?></h3>
                        <p class="text-muted mb-0">Budget</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Campaign Details -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Campaign Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted">Campaign ID</td>
                                <td class="text-end fw-semibold"><?php echo $campaign['campaign_id'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Type</td>
                                <td class="text-end fw-semibold"><?php echo ucfirst($campaign['type'] ?? 'general'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Target Audience</td>
                                <td class="text-end fw-semibold"><?php echo ucfirst($campaign['target_audience'] ?? 'all'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Start Date</td>
                                <td class="text-end fw-semibold"><?php echo isset($campaign['start_date']) ? date('M d, Y', strtotime($campaign['start_date'])) : 'Not set'; ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">End Date</td>
                                <td class="text-end fw-semibold"><?php echo isset($campaign['end_date']) ? date('M d, Y', strtotime($campaign['end_date'])) : 'Not set'; ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Expected Revenue</td>
                                <td class="text-end fw-semibold">₹<?php echo number_format($campaign['expected_revenue'] ?? 0); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Analytics data will be populated once the campaign is launched and starts receiving traffic.
                        </div>
                        
                        <h6 class="mt-3">Quick Actions</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="<?php echo $base; ?>/admin/campaigns/<?php echo $campaign['campaign_id'] ?? ''; ?>/launch" class="btn btn-success w-100" <?php echo ($campaign['status'] ?? '') === 'active' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-rocket me-2"></i>Launch
                                </a>
                            </div>
                            <div class="col-6">
                                <button onclick="exportReport(<?php echo $campaign['campaign_id'] ?? ''; ?>)" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-download me-2"></i>Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Campaign not found.
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportReport(campaignId) {
            alert('Export functionality will be implemented. Campaign ID: ' + campaignId);
        }
    </script>
</body>
</html>
