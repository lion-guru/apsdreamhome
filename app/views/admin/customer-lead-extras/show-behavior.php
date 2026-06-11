<?php
// Session started by controller
$page_title = 'Behavior Analysis Details';
$page_description = 'Detailed view of customer behavior analysis';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Behavior Analysis Details</h1>
            <p class="text-muted">Detailed view of customer behavior analysis</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/behavior" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>

    <?php if ($behavior): ?>
        <!-- Customer Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <img src="<?= BASE_URL ?>/assets/img/default-avatar.png" alt="Avatar" class="img-fluid rounded-circle" />
                    </div>
                    <div class="col-md-9">
                        <h4><?php echo htmlspecialchars($behavior['customer_name'] ?? 'Unknown'); ?></h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo htmlspecialchars($behavior['customer_email'] ?? ''); ?>
                        </p>
                        <?php if (!empty($behavior['customer_phone'])): ?>
                            <p class="text-muted mb-0">
                                <i class="fas fa-phone me-2"></i>
                                <?php echo htmlspecialchars($behavior['customer_phone']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Behavioral Data -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Behavioral Data</h5>
            </div>
            <div class="card-body">
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($behavior['behavioral_data'] ?? 'No data available')); ?></p>
            </div>
        </div>

        <!-- Patterns -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Identified Patterns</h5>
            </div>
            <div class="card-body">
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($behavior['patterns'] ?? 'No patterns identified')); ?></p>
            </div>
        </div>

        <!-- Segmentation -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Customer Segment</h5>
            </div>
            <div class="card-body">
                <span class="badge bg-primary">
                    <?php echo htmlspecialchars($behavior['segmentation'] ?? 'Not Segmented'); ?>
                </span>
            </div>
        </div>

        <!-- Predictions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Predictions</h5>
            </div>
            <div class="card-body">
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($behavior['predictions'] ?? 'No predictions available')); ?></p>
            </div>
        </div>

        <!-- Insights -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Key Insights</h5>
            </div>
            <div class="card-body">
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($behavior['insights'] ?? 'No insights available')); ?></p>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Recommendations</h5>
            </div>
            <div class="card-body">
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($behavior['recommendations'] ?? 'No recommendations available')); ?></p>
            </div>
        </div>

        <!-- Metadata -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Analysis Metadata</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Analysis Date:</strong> <?php echo date('M d, Y H:i', strtotime($behavior['analysis_date'])); ?></p>
                        <p><strong>Created At:</strong> <?php echo date('M d, Y H:i', strtotime($behavior['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Updated At:</strong> <?php echo date('M d, Y H:i', strtotime($behavior['updated_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Analysis Not Found</h4>
            <p>The requested behavior analysis could not be found.</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/behavior" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    <?php endif; ?>
</div>