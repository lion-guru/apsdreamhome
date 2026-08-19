<?php
/**
 * @var array $experiment  Raw experiment row
 * @var array $stats       Payload from ExperimentService::getStats()
 * @var string $csrf_token
 */
$baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$results = $stats['results'] ?? [];
$totals  = $stats['totals']  ?? ['users' => 0, 'conversions' => 0, 'rate' => 0];
$chi     = $stats['chi_square'] ?? ['stat' => 0, 'p_value' => 1, 'significant' => false, 'df' => 0];

$flashSuccess = $_SESSION['success'] ?? $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['error']   ?? $_SESSION['flash_error']   ?? null;
unset($_SESSION['success'], $_SESSION['flash_success'], $_SESSION['error'], $_SESSION['flash_error']);

// Pre-compute variant data for Chart.js
$labels = [];
$users  = [];
$convs  = [];
$rates  = [];
foreach ($results as $variant => $r) {
    $labels[] = $variant;
    $users[]  = (int)($r['users'] ?? 0);
    $convs[]  = (int)($r['conversions'] ?? 0);
    $rates[]  = (float)($r['rate_pct'] ?? 0);
}
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line me-2 text-success"></i>
                Results: <?= htmlspecialchars($experiment['name'] ?? '') ?>
            </h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($experiment['description'] ?? '') ?></p>
        </div>
        <div>
            <a href="<?= $baseUrl ?>/admin/experiments" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <a href="<?= $baseUrl ?>/admin/experiments/<?= (int)$experiment['id'] ?>/export" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
        </div>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flashSuccess ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-times-circle me-1"></i> <?= htmlspecialchars($flashError ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="text-uppercase text-muted small mb-1">Status</div>
                    <?php $st = $experiment['status'] ?? 'draft'; $stBadge = ['draft' => 'secondary', 'running' => 'success', 'ended' => 'dark'][$st] ?? 'secondary'; ?>
                    <h3 class="mb-0"><span class="badge bg-<?= $stBadge ?>"><?= ucfirst($st) ?></span></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="text-uppercase text-muted small mb-1">Total Users</div>
                    <h3 class="mb-0 fw-bold"><?= number_format((int)$totals['users']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="text-uppercase text-muted small mb-1">Conversions</div>
                    <h3 class="mb-0 fw-bold"><?= number_format((int)$totals['conversions']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="text-uppercase text-muted small mb-1">Conversion Rate</div>
                    <h3 class="mb-0 fw-bold"><?= number_format((float)$totals['rate'], 2) ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Variant Performance</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="expResultsChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Chi-Square Test</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($chi['note'])): ?>
                        <p class="text-muted small"><?= htmlspecialchars($chi['note'] ?? '') ?></p>
                    <?php else: ?>
                        <div class="mb-2">
                            <span class="text-muted small">Ï‡Â² statistic:</span>
                            <strong class="float-end"><?= number_format((float)$chi['stat'], 4) ?></strong>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small">Degrees of freedom:</span>
                            <strong class="float-end"><?= (int)$chi['df'] ?></strong>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small">p-value:</span>
                            <strong class="float-end"><?= number_format((float)$chi['p_value'], 5) ?></strong>
                        </div>
                        <div class="alert alert-<?= $chi['significant'] ? 'success' : 'secondary' ?> mb-0">
                            <?php if ($chi['significant']): ?>
                                <i class="fas fa-check-circle me-1"></i>
                                <strong>Significant</strong> at p &lt; 0.05 — results are unlikely due to chance.
                            <?php else: ?>
                                <i class="fas fa-info-circle me-1"></i>
                                Not significant — need more data to draw conclusions.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Per-Variant Breakdown</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Variant</th>
                        <th class="text-end">Users</th>
                        <th class="text-end">Conversions</th>
                        <th class="text-end">Rate</th>
                        <th>Visual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No data yet. Need impressions/clicks to compute stats.</td></tr>
                    <?php else: foreach ($results as $variant => $r):
                        $u = (int)($r['users'] ?? 0);
                        $c = (int)($r['conversions'] ?? 0);
                        $r_pct = (float)($r['rate_pct'] ?? 0);
                        $maxRate = max(array_map(fn($x) => (float)($x['rate_pct'] ?? 0), $results));
                        $width = $maxRate > 0 ? max(5, round(($r_pct / $maxRate) * 100)) : 0;
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($variant ?? '') ?></strong></td>
                            <td class="text-end"><?= number_format($u) ?></td>
                            <td class="text-end"><?= number_format($c) ?></td>
                            <td class="text-end"><strong><?= number_format($r_pct, 2) ?>%</strong></td>
                            <td>
                                <div class="progress" class="style-89219">
                                    <div class="progress-bar bg-primary" class="style-68754"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <form method="POST" action="<?= $baseUrl ?>/admin/experiments/<?= (int)$experiment['id'] ?>/set-winner">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <label class="form-label small">Set Winner Variant</label>
                        <div class="input-group">
                            <select name="winner" class="form-select">
                                <option value="">— pick variant —</option>
                                <?php foreach ($results as $variant => $r): ?>
                                    <option value="<?= htmlspecialchars($variant ?? '') ?>" <?= ($experiment['winner'] ?? '') === $variant ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($variant ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-trophy me-1"></i> Set
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="POST" action="<?= $baseUrl ?>/admin/experiments/<?= (int)$experiment['id'] ?>/end" data-aps-confirm="End this experiment? Existing users keep their variant assignment.">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <label class="form-label small">End Experiment</label>
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-stop-circle me-1"></i> End Experiment
                        </button>
                    </form>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Export Results</label>
                    <a href="<?= $baseUrl ?>/admin/experiments/<?= (int)$experiment['id'] ?>/export" class="btn btn-outline-success w-100">
                        <i class="fas fa-download me-1"></i> Download CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
const ctx = document.getElementById('expResultsChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Users',
                    data: <?= json_encode($users) ?>,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 1,
                    yAxisID: 'y',
                },
                {
                    label: 'Conversions',
                    data: <?= json_encode($convs) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1,
                    yAxisID: 'y',
                },
                {
                    label: 'Conversion Rate %',
                    data: <?= json_encode($rates) ?>,
                    type: 'line',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    backgroundColor: 'rgba(245, 158, 11, 0.2)',
                    yAxisID: 'y1',
                    tension: 0.3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Counts' } },
                y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'Rate %' }, grid: { drawOnChartArea: false } }
            }
        }
    });
}
</script>
