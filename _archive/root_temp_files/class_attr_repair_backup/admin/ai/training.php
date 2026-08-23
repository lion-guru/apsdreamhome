<?php
$page_title = $page_title ?? 'AI Training Dashboard';
$intentCount = $intentCount ?? 0;
$totalIntents = $totalIntents ?? 0;
$learningRecords = $learningRecords ?? 0;
$priceModels = $priceModels ?? 0;
$totalPriceModels = $totalPriceModels ?? 0;
$avgAccuracy = $avgAccuracy ?? 0;
$lastTrained = $lastTrained ?? null;
$totalHits = $totalHits ?? 0;
$totalSuccess = $totalSuccess ?? 0;
$intentAccuracy = $intentAccuracy ?? 0;
$topIntents = $topIntents ?? [];
$models = $models ?? [];
$recentLearning = $recentLearning ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-brain me-2 text-primary"></i>AI Training Dashboard</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/ai/hub" class="btn btn-outline-primary btn-sm"><i class="fas fa-robot me-1"></i>AI Hub</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-brain"></i></span></div>
                    <div><div class="aps-cp-stat-label">Active Intent Patterns</div><div class="aps-cp-stat-value"><?= $intentCount ?></div><div class="aps-cp-stat-meta">Total: <?= $totalIntents ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-database"></i></span></div>
                    <div><div class="aps-cp-stat-label">Learning Records</div><div class="aps-cp-stat-value"><?= number_format($learningRecords) ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-chart-line"></i></span></div>
                    <div><div class="aps-cp-stat-label">Price Models</div><div class="aps-cp-stat-value"><?= $priceModels ?></div><div class="aps-cp-stat-meta">Active of <?= $totalPriceModels ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-bullseye"></i></span></div>
                    <div><div class="aps-cp-stat-label">Intent Accuracy</div><div class="aps-cp-stat-value"><?= $intentAccuracy ?>%</div><div class="aps-cp-stat-meta"><?= number_format($totalHits) ?> hits</div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body text-center">
                <div class="aps-cp-stat-label">Model RÂ² Score</div>
                <div class="aps-cp-stat-value text-<?= $avgAccuracy > 0.7 ? 'success' : ($avgAccuracy > 0.4 ? 'warning' : 'danger') ?>"><?= round($avgAccuracy, 4) ?></div>
                <small class="text-muted">Avg accuracy across models</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body text-center">
                <div class="aps-cp-stat-label">Last Training</div>
                <div class="aps-cp-stat-value fs-6"><?= $lastTrained ? date('d M Y', strtotime($lastTrained)) : 'Never' ?></div>
                <small class="text-muted"><?= $lastTrained ? date('H:i', strtotime($lastTrained)) : '-' ?></small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body text-center">
                <div class="aps-cp-stat-label">Total Hits</div>
                <div class="aps-cp-stat-value"><?= number_format($totalHits) ?></div>
                <small class="text-muted">Successful: <?= number_format($totalSuccess) ?></small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body text-center">
                <div class="aps-cp-stat-label">Languages</div>
                <div class="aps-cp-stat-value">EN + HI</div>
                <small class="text-muted">Hindi + English</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-7">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-bullseye me-2"></i>Top Intent Patterns</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($topIntents)): ?>
                        <div class="text-center text-muted py-4"><i class="fas fa-brain fa-2x mb-2"></i><p>No intent patterns</p></div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Intent</th><th>Type</th><th>Lang</th><th>Hits</th><th>Success</th><th>Accuracy</th></tr></thead>
                                <tbody>
                                <?php foreach ($topIntents as $ti): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ti['intent_name'] ?? '') ?></strong></td>
                                        <td><span class="aps-cp-badge badge bg-secondary"><?= htmlspecialchars($ti['pattern_type'] ?? '') ?></span></td>
                                        <td><span class="aps-cp-badge badge bg-info"><?= strtoupper(htmlspecialchars($ti['language'] ?? '')) ?></span></td>
                                        <td><?= number_format($ti['hit_count']) ?></td>
                                        <td><?= number_format($ti['success_count']) ?></td>
                                        <td><?php $acc = $ti['hit_count'] > 0 ? round($ti['success_count']/$ti['hit_count']*100) : 0; ?><span class="text-<?= $acc > 70 ? 'success' : ($acc > 40 ? 'warning' : 'danger') ?>"><?= $acc ?>%</span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-chart-bar me-2"></i>Learning Data by Action</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($recentLearning)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No learning data</div>
                    <?php else: ?>
                        <?php foreach ($recentLearning as $rl): ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between"><small class="text-capitalize"><?= htmlspecialchars($rl['action_type'] ?? '') ?></small><small><?= $rl['cnt'] ?> records</small></div>
                                <div class="progress" class="style-51910"><div class="progress-bar bg-primary" class="style-74689"></div></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-chart-line me-2"></i>Price Models</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($models)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No models trained</div>
                    <?php else: ?>
                        <?php foreach (array_slice($models, 0, 5) as $m): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div><strong class="small"><?= htmlspecialchars($m['property_type'] ?? '') ?></strong><br><small class="text-muted">Samples: <?= $m['sample_size'] ?></small></div>
                                <span class="aps-cp-badge badge bg-<?= $m['r_squared'] > 0.7 ? 'success' : ($m['r_squared'] > 0.4 ? 'warning' : 'danger') ?>">RÂ² <?= round($m['r_squared'], 3) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
