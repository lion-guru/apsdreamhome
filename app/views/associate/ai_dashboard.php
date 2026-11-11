<?php
// AI Dashboard View for Associates
// Data: $priority_leads, $churn_alerts, $next_best_actions, $deal_predictions, $performance_score, $ai_insights, $recommendations
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-brain text-primary me-2"></i>AI Dashboard</h1>
            <p class="text-muted small mb-0">AI-powered insights, priority leads, and next best actions</p>
        </div>
        <a href="<?= BASE_URL ?>/associate/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Performance Score Card -->
    <?php if (!empty($performance_score)): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Performance Score</h6>
                    <div class="display-4 fw-bold text-<?= ($performance_score['score'] ?? 0) >= 70 ? 'success' : (($performance_score['score'] ?? 0) >= 40 ? 'warning' : 'danger') ?>">
                        <?= $performance_score['score'] ?? 0 ?>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-<?= ($performance_score['score'] ?? 0) >= 70 ? 'success' : (($performance_score['score'] ?? 0) >= 40 ? 'warning' : 'danger') ?>" style="width: <?= $performance_score['score'] ?? 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Leads</h6>
                    <div class="display-4 fw-bold text-primary"><?= $performance_score['total_leads'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Closed Deals</h6>
                    <div class="display-4 fw-bold text-success"><?= $performance_score['closed_deals'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Conversion Rate</h6>
                    <div class="display-4 fw-bold text-info"><?= $performance_score['conversion_rate'] ?? 0 ?>%</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- AI Insights -->
    <?php if (!empty($ai_insights)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="fas fa-lightbulb text-warning me-2"></i>AI Insights</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($ai_insights as $insight): ?>
                    <div class="alert alert-<?= $insight['color'] ?? 'info' ?> d-flex align-items-center mb-2">
                        <i class="<?= $insight['icon'] ?? 'fas fa-info-circle' ?> me-2"></i>
                        <div>
                            <strong><?= htmlspecialchars($insight['title'] ?? '') ?></strong>
                            <div class="small"><?= htmlspecialchars($insight['message'] ?? '') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Next Best Actions -->
    <?php if (!empty($next_best_actions)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="fas fa-tasks text-primary me-2"></i>Next Best Actions</h5>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($next_best_actions as $action): ?>
                    <a href="<?= BASE_URL . ($action['action_url'] ?? '#') ?>" class="d-flex align-items-center p-3 border-bottom text-decoration-none hover-bg-light">
                        <div class="rounded-circle bg-<?= $action['color'] ?? 'primary' ?> bg-opacity-10 p-2 me-3">
                            <i class="<?= $action['icon'] ?? 'fas fa-arrow-right' ?> text-<?= $action['color'] ?? 'primary' ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?= htmlspecialchars($action['title'] ?? '') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($action['description'] ?? '') ?></div>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Churn Alerts -->
    <?php if (!empty($churn_alerts)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Churn Alerts</h5>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($churn_alerts as $alert): ?>
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <div class="rounded-circle bg-<?= $alert['severity'] === 'high' ? 'danger' : 'warning' ?> bg-opacity-10 p-2 me-3">
                            <i class="fas fa-exclamation text-<?= $alert['severity'] === 'high' ? 'danger' : 'warning' ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?= htmlspecialchars($alert['title'] ?? '') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($alert['message'] ?? '') ?></div>
                        </div>
                        <?php if (!empty($alert['action_url'])): ?>
                        <a href="<?= BASE_URL . $alert['action_url'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Priority Leads -->
    <?php if (!empty($priority_leads)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-star text-warning me-2"></i>Priority Leads</h5>
                    <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Lead</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Days Stale</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($priority_leads as $lead): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars($lead['phone'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($lead['score'] ?? 0) >= 70 ? 'success' : (($lead['score'] ?? 0) >= 40 ? 'warning' : 'secondary') ?>">
                                            <?= $lead['score'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($lead['status'] ?? '') ?></span></td>
                                    <td><?= $lead['days_since_activity'] ?? 0 ?> days</td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/associate/leads/<?= $lead['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Deal Predictions -->
    <?php if (!empty($deal_predictions)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="fas fa-chart-line text-success me-2"></i>Deal Predictions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Lead</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Probability</th>
                                    <th>Risk Factors</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deal_predictions as $pred): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pred['lead_name'] ?? 'Unknown') ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($pred['status'] ?? '') ?></span></td>
                                    <td>₹<?= number_format($pred['total_amount'] ?? 0) ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($pred['probability'] ?? 0) >= 70 ? 'success' : (($pred['probability'] ?? 0) >= 40 ? 'warning' : 'danger') ?>">
                                            <?= $pred['probability'] ?? 0 ?>%
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= implode(', ', $pred['risk_factors'] ?? []) ?: 'None' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- AI Recommendations -->
    <?php if (!empty($recommendations)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0"><i class="fas fa-lightbulb text-info me-2"></i>AI Recommendations</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($recommendations as $rec): ?>
                    <div class="alert alert-info d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle me-2"></i>
                        <div><?= htmlspecialchars(is_string($rec) ? $rec : ($rec['message'] ?? json_encode($rec))) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
