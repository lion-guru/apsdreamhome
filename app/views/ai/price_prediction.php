<?php
/**
 * AI Price Prediction View
 */
$property = $property ?? [];
$current_prediction = $current_prediction ?? [];
$market_trends = $market_trends ?? [];
$prediction_accuracy = $prediction_accuracy ?? 0;
$page_title = $page_title ?? 'AI Price Prediction - APS Dream Home';
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
        .prediction-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .factor-badge {
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-brain me-2 text-primary"></i>AI Price Prediction</h2>
                <p class="text-muted mb-0">Machine learning powered property valuation</p>
            </div>
            <a href="<?php echo $base; ?>/properties" class="btn btn-outline-secondary">Back to Properties</a>
        </div>

        <?php if (!empty($property)): ?>
            <!-- Property Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3"><?php echo htmlspecialchars($property['title'] ?? 'Property'); ?></h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Location</p>
                            <p><?php echo htmlspecialchars($property['location'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Current Price</p>
                            <h5 class="text-primary">₹<?php echo number_format($property['price'] ?? 0); ?></h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Type</p>
                            <p><?php echo htmlspecialchars($property['type'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prediction Result -->
            <div class="row">
                <div class="col-md-8">
                    <div class="prediction-card p-4 mb-4">
                        <h4 class="mb-3"><i class="fas fa-robot me-2"></i>AI Prediction Result</h4>
                        <?php if (!empty($current_prediction)): ?>
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <p class="mb-1">Predicted Price</p>
                                    <h2 class="mb-0">₹<?php echo number_format($current_prediction['predicted_price'] ?? 0); ?></h2>
                                    <small>Confidence: <?php echo $current_prediction['accuracy'] ?? 87.5; ?>%</small>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">Price Range</p>
                                    <h5>₹<?php echo number_format($current_prediction['confidence_range']['lower'] ?? 0); ?> - ₹<?php echo number_format($current_prediction['confidence_range']['upper'] ?? 0); ?></h5>
                                </div>
                            </div>
                            <hr class="my-3 bg-white opacity-25">
                            <h6>Factors Considered:</h6>
                            <div class="d-flex gap-2 flex-wrap mt-2">
                                <span class="factor-badge"><i class="fas fa-map-marker-alt me-1"></i>Location</span>
                                <span class="factor-badge"><i class="fas fa-home me-1"></i>Property Features</span>
                                <span class="factor-badge"><i class="fas fa-chart-line me-1"></i>Market Trends</span>
                                <span class="factor-badge"><i class="fas fa-calendar me-1"></i>Seasonality</span>
                            </div>
                        <?php else: ?>
                            <p>Click below to generate AI prediction</p>
                            <button class="btn btn-light" onclick="generatePrediction()">
                                <i class="fas fa-magic me-2"></i>Generate Prediction
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Market Trends -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Market Trends</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($market_trends)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Period</th><th>Trend</th><th>Change</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($market_trends as $trend): ?>
                                                <tr>
                                                    <td><?php echo $trend['period'] ?? '-'; ?></td>
                                                    <td><?php echo $trend['trend'] ?? '-'; ?></td>
                                                    <td class="text-<?php echo ($trend['change'] ?? 0) >= 0 ? 'success' : 'danger'; ?>">
                                                        <?php echo ($trend['change'] ?? 0) > 0 ? '+' : ''; ?><?php echo $trend['change'] ?? 0; ?>%
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No market trend data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Prediction Accuracy -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Model Accuracy</h6>
                            <h2 class="text-success mb-2"><?php echo $prediction_accuracy; ?>%</h2>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $prediction_accuracy; ?>%"></div>
                            </div>
                            <small class="text-muted">Based on historical predictions</small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="generatePrediction()">
                                    <i class="fas fa-sync me-2"></i>Refresh Prediction
                                </button>
                                <a href="<?php echo $base; ?>/ai/property-valuation/history" class="btn btn-outline-secondary">
                                    <i class="fas fa-history me-2"></i>View History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>No Property Selected</h5>
                <p>Please select a property to get AI price prediction.</p>
                <a href="<?php echo $base; ?>/properties" class="btn btn-primary">Browse Properties</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generatePrediction() {
            alert('AI prediction generation would be triggered here via API call');
        }
    </script>
</body>
</html>
