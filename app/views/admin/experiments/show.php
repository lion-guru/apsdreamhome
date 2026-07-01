<?php
/**
 * @var array $experiment
 * @var array $stats
 */
$pageTitle    = $page_title ?? 'Experiment Results';
$baseUrl      = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$csrf         = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
$flashSuccess = $_SESSION['success'] ?? $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['error']   ?? $_SESSION['flash_error']   ?? null;
unset($_SESSION['success'], $_SESSION['flash_success'], $_SESSION['error'], $_SESSION['flash_error']);

$status    = $experiment['status'] ?? 'draft';
$badgeMap  = ['draft' => 'secondary', 'running' => 'success', 'ended' => 'dark'];
$badge     = $badgeMap[$status] ?? 'secondary';
$results   = $stats['results'] ?? [];
$totals    = $stats['totals']  ?? ['users' => 0, 'conversions' => 0, 'rate' => 0];
$chi       = $stats['chi_square'] ?? null;
$variantNames = array_keys($results);
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-bar me-2 text-primary"></i><?= htmlspecialchars($experiment['name']) ?>
                <span class="badge bg-<?= $badge ?> ms-2"><?= ucfirst($status) ?></span>
                <?php if (!empty($experiment['winner'])): ?>
                    <span class="badge bg-warning text-dark ms-1"><i class="fas fa-trophy me-1"></i><?= htmlspecialchars($experiment['winner']) ?></span>
                <?php endif; ?>
            </h1>
            <?php if (!empty($experiment['description'])): ?>
                <p class="text-muted mb-0"><?= htmlspecialchars($experiment['description']) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= $baseUrl ?>/admin/experiments" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> All Experiments</a>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-times-circle me-1"></i> <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Total Users</div>
                    <h3 class="mb-0 fw-bold text-primary"><?= number_format($totals['users']) ?></h3>
                    <div class="text-muted small mt-1">Traffic allocation: <?= (int)($experiment['traffic_allocation'] ?? 100) ?>%</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Total Conversions</div>
                    <h3 class="mb-0 fw-bold text-success"><?= number_format($totals['conversions']) ?></h3>
                    <div class="text-muted small mt-1">Overall rate: <?= number_format((float)($totals['rate'] ?? 0), 2) ?>%</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Variants</div>
                    <h3 class="mb-0 fw-bold text-info"><?= count($variantNames) ?></h3>
                    <div class="text-muted small mt-1"><?= htmlspecialchars(implode(', ', $variantNames)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="text-muted small">Statistical Significance</div>
                    <?php if ($chi && isset($chi['stat'])): ?>
                        <h3 class="mb-0 fw-bold text-<?= !empty($chi['significant']) ? 'success' : 'warning' ?>">
                            p = <?= number_format((float)$chi['p_value'], 4) ?>
                        </h3>
                        <div class="small mt-1">
                            <?php if (!empty($chi['significant'])): ?>
                                <span class="badge bg-success">Significant (p &lt; 0.05)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Not yet significant</span>
                            <?php endif; ?>
                            <span class="text-muted">χ² = <?= number_format((float)$chi['stat'], 2) ?>, df = <?= (int)$chi['df'] ?></span>
                        </div>
                    <?php else: ?>
                        <h3 class="mb-0 text-muted">—</h3>
                        <div class="text-muted small mt-1">No data yet</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-chart-column me-1"></i> Conversion Rate by Variant</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($results)): ?>
                        <div class="text-center text-muted py-5">No data yet. Variants will be assigned as users visit pages with this experiment.</div>
                    <?php else: ?>
                        <canvas id="conversionChart" style="max-height: 360px;"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-table me-1"></i> Per-Variant Stats</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Variant</th>
                                <th class="text-end">Users</th>
                                <th class="text-end">Conv.</th>
                                <th class="text-end">Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $bestRate = 0;
                                foreach ($results as $r) $bestRate = max($bestRate, (float)($r['rate'] ?? 0));
                            ?>
                            <?php foreach ($results as $name => $r): ?>
                                <?php $isBest = $bestRate > 0 && abs(((float)$r['rate']) - $bestRate) < 1e-9; ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($name) ?></strong>
                                        <?php if ($isBest && count($results) > 1): ?>
                                            <i class="fas fa-trophy text-warning ms-1" title="Highest rate"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?= number_format((int)$r['users']) ?></td>
                                    <td class="text-end"><?= number_format((int)$r['conversions']) ?></td>
                                    <td class="text-end fw-semibold"><?= number_format((float)$r['rate_pct'], 2) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>

            <?php if ($status === 'running'): ?>
                <div class="card border-warning shadow-sm">
                    <div class="card-header bg-warning bg-opacity-25"><h6 class="mb-0"><i class="fas fa-flag-checkered me-1"></i> End Experiment</h6></div>
                    <div class="card-body aps-cp-card-body">
                        <form method="POST" action="<?= $baseUrl ?>/admin/experiments/<?= (int)$experiment['id'] ?>/end"
                              onsubmit="return confirm('End this experiment? You can still view results afterwards.');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <div class="mb-2">
                                <label class="form-label small">Declare winner (optional)</label>
                                <select name="winner" class="form-select form-select-sm">
                                    <option value="">— No winner —</option>
                                    <?php foreach ($variantNames as $vn): ?>
                                        <option value="<?= htmlspecialchars($vn) ?>"><?= htmlspecialchars($vn) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm w-100"><i class="fas fa-stop me-1"></i> End Experiment</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($results)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const ctx = document.getElementById('conversionChart');
    if (!ctx) return;
    const labels = <?= json_encode(array_keys($results)) ?>;
    const rates  = <?= json_encode(array_map(fn($r) => (float)$r['rate_pct'], $results)) ?>;
    const users  = <?= json_encode(array_map(fn($r) => (int)$r['users'], $results)) ?>;
    const convs  = <?= json_encode(array_map(fn($r) => (int)$r['conversions'], $results)) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Conversion Rate (%)',
                data: rates,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Users',
                data: users,
                backgroundColor: 'rgba(108, 117, 125, 0.4)',
                borderColor: 'rgba(108, 117, 125, 1)',
                borderWidth: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y:  { beginAtZero: true, title: { display: true, text: 'Conversion %' }, position: 'left' },
                y1: { beginAtZero: true, title: { display: true, text: 'Users' }, position: 'right', grid: { drawOnChartArea: false } }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        afterLabel: function(ctx) {
                            const i = ctx.dataIndex;
                            return 'Conversions: ' + convs[i];
                        }
                    }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>
