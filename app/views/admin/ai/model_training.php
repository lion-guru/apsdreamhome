<?php
/**
 * Admin AI Model Training View
 */
$model_stats = $model_stats ?? [];
$page_title = $page_title ?? 'AI Model Training - APS Dream Home';
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
                <h2 class="mb-1"><i class="fas fa-brain me-2 text-primary"></i>AI Model Training</h2>
                <p class="text-muted mb-0">Manage and train machine learning models</p>
            </div>
            <a href="<?php echo $base; ?>/admin/dashboard" class="btn btn-outline-secondary">Back to Admin</a>
        </div>

        <!-- Model Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-robot fa-2x text-primary mb-2"></i>
                        <h4><?php echo $model_stats['total_models'] ?? 5; ?></h4>
                        <p class="text-muted mb-0">Total Models</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h4><?php echo $model_stats['active_models'] ?? 4; ?></h4>
                        <p class="text-muted mb-0">Active Models</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                        <h4><?php echo $model_stats['avg_accuracy'] ?? '87.5%'; ?></h4>
                        <p class="text-muted mb-0">Avg Accuracy</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-database fa-2x text-warning mb-2"></i>
                        <h4><?php echo number_format($model_stats['training_data_size'] ?? 12500); ?></h4>
                        <p class="text-muted mb-0">Training Records</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Models List -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-cubes me-2"></i>ML Models</h5>
                        <form method="POST" action="<?php echo $base; ?>/admin/ai/model-training" class="m-0">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-sync me-2"></i>Train All Models
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($model_stats['models'])): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Model</th>
                                            <th>Status</th>
                                            <th>Accuracy</th>
                                            <th>Last Trained</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($model_stats['models'] as $model): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($model['name'] ?? 'N/A'); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($model['type'] ?? ''); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($model['status'] ?? '') === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($model['status'] ?? 'inactive'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-<?php echo ($model['accuracy'] ?? 0) > 80 ? 'success' : (($model['accuracy'] ?? 0) > 60 ? 'warning' : 'danger'); ?>" 
                                                             style="width: <?php echo $model['accuracy'] ?? 0; ?>%">
                                                            <?php echo $model['accuracy'] ?? 0; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo isset($model['last_trained']) ? date('M d, Y', strtotime($model['last_trained'])) : 'Never'; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="trainModel('<?php echo $model['id'] ?? ''; ?>')">
                                                        <i class="fas fa-play"></i> Train
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-cube fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No models configured</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Training Info -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Training Info</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>Price Prediction Model
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>Recommendation Engine
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>Market Analysis Model
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>Valuation Model
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-clock text-warning me-2"></i>Demand Forecasting
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Training</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0">
                                <small class="text-muted"><?php echo date('M d, Y'); ?></small>
                                <p class="mb-0">Price Prediction - 92% accuracy</p>
                            </div>
                            <div class="list-group-item px-0">
                                <small class="text-muted"><?php echo date('M d, Y', strtotime('-1 day')); ?></small>
                                <p class="mb-0">Recommendation - 88% accuracy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function trainModel(modelId) {
            alert('Training model ' + modelId + ' - This would trigger training via API');
        }
    </script>
</body>
</html>
