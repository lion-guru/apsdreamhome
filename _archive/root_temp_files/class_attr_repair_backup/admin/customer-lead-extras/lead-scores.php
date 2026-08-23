<?php
// Session started by controller
$page_title = 'AI Lead Scoring';
$page_description = 'Monitor and manage AI-powered lead scores';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">AI Lead Scoring</h1>
            <p class="text-muted">Monitor and manage AI-powered lead scores</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-tachometer-alt fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Average Score</h6>
                            <h3 class="mb-0"><?php echo number_format($stats['avg_score'] ?? 0, 1); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">High Risk (80+)</h6>
                            <h3 class="mb-0"><?php echo $stats['high_risk_count'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-balance-scale fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Medium Score</h6>
                            <h3 class="mb-0"><?php echo $stats['medium_score_count'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-chart-pie fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Low Score</h6>
                            <h3 class="mb-0"><?php echo $stats['low_score_count'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Score Distribution Chart (Placeholder) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Score Distribution</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="progress style-4902">
                <div class="progress-bar bg-success style-53568">
                    Low (0-59): <?php echo $stats['low_score_count'] ?? 0; ?>
                </div>
                <div class="progress-bar bg-warning style-56235">
                    Medium (60-79): <?php echo $stats['medium_score_count'] ?? 0; ?>
                </div>
                <div class="progress-bar bg-danger style-66378">
                    High (80-100): <?php echo $stats['high_risk_count'] ?? 0; ?>
                </div>
            </div>
            <small class="text-muted d-block mt-2">Total: <?php echo (($stats['low_score_count'] ?? 0) + ($stats['medium_score_count'] ?? 0) + ($stats['high_risk_count'] ?? 0)); ?> leads scored</small>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lead Scores</h5>
                <a href="<?php echo BASE_URL; ?>/admin/customer-lead/lead-scores" class="btn btn-outline-primary btn-sm">Refresh</a>
            </div>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Lead Name</th>
                            <th>Score</th>
                            <th>Criteria</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($leadScores)): ?>
                            <?php foreach ($leadScores as $score): ?>
                                <?php
                                $scoreValue = $score['score'] ?? 0;
                                $scoreClass = $scoreValue >= 80 ? 'bg-danger' : ($scoreValue >= 60 ? 'bg-warning' : 'bg-success');
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img src="<?= BASE_URL ?>/assets/images/user/default-avatar.jpg" alt="Avatar" class="rounded-circle" width="32" height="32" />
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($score['lead_name'] ?? 'Unknown'); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($score['lead_email'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress style-48235">
                                            <div class="progress-bar <?php echo e($scoreClass); ?>" class="style-58620"></div>
                                        </div>
                                        <span class="ms-2"><?php echo e($scoreValue); ?>%</span>
                                    </td>
                                    <td>
                                        <div class="text-truncate style-65684" title="<?php echo htmlspecialchars($score['criteria'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($score['criteria'] ?? 'No criteria'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($score['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/lead-scores/<?php echo e($score['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/lead-scores/edit/<?php echo e($score['id']); ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-muted mb-0">No lead scores found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>