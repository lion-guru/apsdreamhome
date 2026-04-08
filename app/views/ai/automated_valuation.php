<?php
/**
 * AI Automated Valuation View
 */
$property = $property ?? [];
$valuation_data = $valuation_data ?? [];
$page_title = $page_title ?? 'Automated Property Valuation - APS Dream Home';
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
    <style>
        .valuation-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-calculator me-2 text-success"></i>Automated Valuation</h2>
                <p class="text-muted mb-0">AI-powered property assessment</p>
            </div>
            <a href="<?php echo $base; ?>/properties" class="btn btn-outline-secondary">Back</a>
        </div>

        <?php if (!empty($property)): ?>
            <div class="row">
                <div class="col-md-8">
                    <!-- Property Details -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><?php echo htmlspecialchars($property['title'] ?? 'Property'); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Location</p>
                                    <p><?php echo htmlspecialchars($property['location'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Type</p>
                                    <p><?php echo htmlspecialchars($property['type'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Area</p>
                                    <p><?php echo $property['area'] ?? 'N/A'; ?> sq ft</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Bedrooms</p>
                                    <p><?php echo $property['bedrooms'] ?? 'N/A'; ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Listed Price</p>
                                    <h6 class="text-primary">₹<?php echo number_format($property['price'] ?? 0); ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Valuation -->
                    <?php if (!empty($valuation_data)): ?>
                        <div class="valuation-card p-4 mb-4">
                            <h4 class="mb-3"><i class="fas fa-robot me-2"></i>AI Valuation Result</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">Estimated Value</p>
                                    <h2 class="mb-0">₹<?php echo number_format($valuation_data['estimated_value'] ?? 0); ?></h2>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">Price per Sq Ft</p>
                                    <h4>₹<?php echo number_format($valuation_data['price_per_sqft'] ?? 0); ?></h4>
                                </div>
                            </div>
                            <hr class="my-3 bg-white opacity-25">
                            <div class="row">
                                <div class="col-md-4">
                                    <small>Confidence Score</small>
                                    <h5><?php echo $valuation_data['confidence'] ?? 85; ?>%</h5>
                                </div>
                                <div class="col-md-4">
                                    <small>Market Comparison</small>
                                    <h5><?php echo $valuation_data['market_comparison'] ?? 'Fair'; ?></h5>
                                </div>
                                <div class="col-md-4">
                                    <small>Valuation Date</small>
                                    <h5><?php echo date('M d, Y'); ?></h5>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Valuation data not available. Run AI valuation to get results.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <!-- Valuation Factors -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Valuation Factors</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($valuation_data['factors'])): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($valuation_data['factors'] as $factor => $impact): ?>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span><?php echo ucfirst(str_replace('_', ' ', $factor)); ?></span>
                                            <span class="badge bg-<?php echo $impact > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $impact > 0 ? '+' : ''; ?><?php echo $impact; ?>%
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">Factors will be analyzed during valuation</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="runValuation()">
                            <i class="fas fa-play me-2"></i>Run Valuation
                        </button>
                        <a href="<?php echo $base; ?>/ai/property-valuation/history" class="btn btn-outline-secondary">
                            <i class="fas fa-history me-2"></i>View History
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <h5>Property Not Found</h5>
                <p>The requested property could not be found.</p>
                <a href="<?php echo $base; ?>/properties" class="btn btn-primary">Browse Properties</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function runValuation() {
            alert('Automated valuation would run here via API');
        }
    </script>
</body>
</html>
