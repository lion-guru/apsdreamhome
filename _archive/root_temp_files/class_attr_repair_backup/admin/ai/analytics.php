<?php
$analytics_data = $analytics_data ?? [];
$predictions = $predictions ?? [];
$page_title = $page_title ?? 'AI Analytics';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">AI Analytics</h2>
                <p class="text-muted mb-0">AI-powered insights and predictions</p>
            </div>
            <a href="<?php echo e($base); ?>/admin/ai/hub" class="btn btn-outline-secondary">Back to AI Hub</a>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-bullseye fa-2x text-primary mb-2"></i>
                        <h3 class="mb-1"><?php echo $analytics_data['prediction_accuracy'] ?? 0; ?>%</h3>
                        <p class="text-muted mb-0">Prediction Accuracy</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                        <h3 class="mb-1"><?php echo $analytics_data['conversion_rate'] ?? 0; ?>%</h3>
                        <p class="text-muted mb-0">Lead Conversion Rate</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-brain fa-2x text-info mb-2"></i>
                        <h3 class="mb-1"><?php echo count($predictions); ?></h3>
                        <p class="text-muted mb-0">Active Predictions</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Predictions Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-magic me-2"></i>AI Predictions</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($predictions)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Prediction</th>
                                    <th>Type</th>
                                    <th>Confidence</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($predictions as $prediction): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($prediction['prediction_text'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($prediction['prediction_type'] ?? '-'); ?></td>
                                        <td>
                                            <div class="progress" class="style-51309">
                                                <div class="progress-bar bg-success" class="style-77223">
                                                    <?php echo $prediction['confidence_score'] ?? 0; ?>%
    </div>
                                        </td>
                                        <td><?php echo isset($prediction['prediction_date']) ? date('M d, Y', strtotime($prediction['prediction_date'])) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-magic fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No predictions available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
