<?php
/** Admin Engagement Survey Analytics */

$page_title = $page_title ?? 'Survey Analytics';
$analytics = $analytics ?? [];
$departments = $departments ?? [];
$trend = $trend ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.chart-bar {
    height: 24px;
    border-radius: 4px;
    background: linear-gradient(90deg, #7c2d12, #b45309);
    transition: width 0.3s ease;
}
.chart-bar-success { background: linear-gradient(90deg, #166534, #22c55e); }
.chart-bar-warning { background: linear-gradient(90deg, #854d0e, #f59e0b); }
.chart-bar-danger { background: linear-gradient(90deg, #991b1b, #ef4444); }
.enps-score { font-size: 3rem; font-weight: 700; }
.enps-positive { color: #16a34a; }
.enps-negative { color: #dc2626; }
.enps-neutral { color: #f59e0b; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-chart-bar me-2 text-primary"></i>Survey Analytics</h4>
            <p class="text-muted mb-0 small">
                <?= htmlspecialchars($analytics['survey']['title'] ?? 'Survey') ?>
                <span class="badge bg-<?= 
                    ($analytics['survey']['status'] ?? '') === 'active' ? 'success' : 
                    (($analytics['survey']['status'] ?? '') === 'draft' ? 'secondary' : 'warning') 
                ?> ms-2">
                    <?= ucfirst($analytics['survey']['status'] ?? 'unknown') ?>
                </span>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/admin/engagement/surveys" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Surveys
        </a>
    </div>

    <?php if (isset($analytics['success']) && !$analytics['success']): ?>
        <div class="alert alert-danger">Error: <?= htmlspecialchars($analytics['message'] ?? 'Unknown error') ?></div>
        <a href="<?= BASE_URL ?>/admin/engagement/surveys" class="btn btn-primary">Back</a>
        <?php return; ?>
    <?php endif; ?>

    <!-- Overview Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $analytics['total_responses'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Responses</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count($analytics['questions_analysis'] ?? []) ?></div>
                    <div class="text-white-50 small">Questions</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <?php 
                    $avgCompletion = $analytics['completion_stats']['avg_seconds'] ?? 0;
                    $avgMin = $avgCompletion > 0 ? round($avgCompletion / 60, 1) : 0;
                    ?>
                    <div class="fw-bold fs-3"><?= $avgMin ?> min</div>
                    <div class="text-white-50 small">Avg Completion Time</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-<?= 
                ($analytics['enps'] ?? 0) > 0 ? 'success' : 
                (($analytics['enps'] ?? 0) < 0 ? 'danger' : 'warning') 
            ?> text-white">
                <div class="card-body text-center">
                    <div class="enps-score enps-<?= ($analytics['enps'] ?? 0) > 0 ? 'positive' : (($analytics['enps'] ?? 0) < 0 ? 'negative' : 'neutral') ?>">
                        <?= $analytics['enps'] ?? 0 ?>
                    </div>
                    <div class="text-white-50 small">eNPS Score</div>
                </div>
            </div>
        </div>
    </div>

    <!-- eNPS Breakdown -->
    <?php if (!empty($analytics['enps_breakdown'])): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-chart-pie me-2"></i>eNPS Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="card stat-card border-0 shadow-sm bg-success text-white">
                        <div class="card-body">
                            <div class="fw-bold fs-2"><?= $analytics['enps_breakdown']['promoters'] ?? 0 ?></div>
                            <div class="text-white-50 small">Promoters (9-10)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                        <div class="card-body">
                            <div class="fw-bold fs-2"><?= $analytics['enps_breakdown']['passives'] ?? 0 ?></div>
                            <div class="text-white-50 small">Passives (7-8)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card border-0 shadow-sm bg-danger text-white">
                        <div class="card-body">
                            <div class="fw-bold fs-2"><?= $analytics['enps_breakdown']['detractors'] ?? 0 ?></div>
                            <div class="text-white-50 small">Detractors (0-6)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Question Analysis -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-question-circle me-2"></i>Question Analysis</h5>
        </div>
        <div class="card-body">
            <?php if (empty($analytics['questions_analysis'])): ?>
            <div class="text-center py-4 text-muted">No question data available</div>
            <?php else: ?>
            <?php foreach ($analytics['questions_analysis'] as $i => $qa): ?>
            <div class="mb-4 pb-4 border-bottom">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-info">Q<?= $i + 1 ?></span>
                        <span class="question-type-badge bg-secondary"><?= strtoupper($qa['question']['type'] ?? 'rating') ?></span>
                    </div>
                    <div class="small text-muted"><?= $qa['responses'] ?? 0 ?> responses</div>
                </div>
                <div class="fw-semibold mb-3"><?= htmlspecialchars($qa['question']['text'] ?? 'Untitled Question') ?></div>
                
                <?php if (!empty($qa['distribution'])): ?>
                    <div class="row g-2">
                        <?php 
                        $total = $qa['responses'] ?? 1;
                        foreach ($qa['distribution'] as $key => $count): 
                            $pct = $total > 0 ? round(($count / $total) * 100) : 0;
                        ?>
                        <div class="col-md-3">
                            <div class="small fw-semibold"><?= htmlspecialchars($key) ?></div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-<?= 
                                    $key === 'Yes' || $key === '5' || $key === 'Strongly Agree' ? 'success' : 
                                    ($key === 'No' || $key === '1' || $key === 'Strongly Disagree' ? 'danger' : 'primary') 
                                ?>" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted"><?= $count ?> (<?= $pct ?>%)</small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($qa['average'] !== null): ?>
                    <div class="mt-2">
                        <span class="badge bg-primary">Average: <?= $qa['average'] ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Department Breakdown -->
    <?php if (!empty($departments)): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-building me-2"></i>Department Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th>Responses</th>
                            <th>Avg Score</th>
                            <th>Participation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalResponses = $analytics['total_responses'] ?? 1;
                        foreach ($departments as $dept => $data): 
                            $pct = round(($data['count'] / $totalResponses) * 100, 1);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($dept) ?></td>
                            <td><?= $data['count'] ?></td>
                            <td><?= $data['avg_score'] !== null ? number_format($data['avg_score'], 2) : '—' ?></td>
                            <td>
                                <div class="progress" style="height: 8px; width: 100px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted"><?= $pct ?>%</small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Trend Data -->
    <?php if (!empty($trend)): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-chart-line me-2"></i>Response Trend</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Responses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trend as $t): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($t['date'])) ?></td>
                            <td><?= $t['responses'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>