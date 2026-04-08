<?php
/**
 * Admin AI Market Analysis View
 */
$market_data = $market_data ?? [];
$page_title = $page_title ?? 'AI Market Analysis - APS Dream Home';
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
                <h2 class="mb-1"><i class="fas fa-chart-line me-2 text-primary"></i>AI Market Analysis</h2>
                <p class="text-muted mb-0">Comprehensive market insights powered by AI</p>
            </div>
            <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back to Admin</a>
        </div>

        <!-- Overview Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-arrow-trend-up fa-2x text-success mb-2"></i>
                        <h4><?php echo $market_data['price_trends']['overall'] ?? '+5.2%'; ?></h4>
                        <p class="text-muted mb-0">Price Trend</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-info mb-2"></i>
                        <h4><?php echo $market_data['demand_forecast']['current_month'] ?? 'High'; ?></h4>
                        <p class="text-muted mb-0">Demand Level</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-star fa-2x text-warning mb-2"></i>
                        <h4><?php echo count($market_data['investment_opportunities'] ?? []); ?></h4>
                        <p class="text-muted mb-0">Investment Opportunities</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt fa-2x text-danger mb-2"></i>
                        <h4><?php echo $market_data['risk_assessment']['overall'] ?? 'Low'; ?></h4>
                        <p class="text-muted mb-0">Risk Level</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Price Trends -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Price Trends Analysis</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($market_data['price_trends'])): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($market_data['price_trends'] as $location => $trend): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($location); ?></span>
                                        <span class="badge bg-<?php echo $trend > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $trend > 0 ? '+' : ''; ?><?php echo $trend; ?>%
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No price trend data available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Investment Opportunities -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-gem me-2"></i>Investment Opportunities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($market_data['investment_opportunities'])): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($market_data['investment_opportunities'] as $opp): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($opp['location'] ?? 'N/A'); ?></h6>
                                            <span class="badge bg-success">ROI: <?php echo $opp['roi'] ?? 0; ?>%</span>
                                        </div>
                                        <small class="text-muted"><?php echo htmlspecialchars($opp['reason'] ?? ''); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No investment opportunities identified</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk Assessment -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Risk Assessment</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($market_data['risk_assessment'])): ?>
                    <div class="row">
                        <?php foreach ($market_data['risk_assessment'] as $risk_type => $risk_level): ?>
                            <?php if (is_array($risk_level)) continue; ?>
                            <div class="col-md-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                    <span><?php echo ucfirst(str_replace('_', ' ', $risk_type)); ?></span>
                                    <span class="badge bg-<?php echo strtolower($risk_level) === 'low' ? 'success' : (strtolower($risk_level) === 'medium' ? 'warning' : 'danger'); ?>">
                                        <?php echo $risk_level; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Risk assessment data not available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
