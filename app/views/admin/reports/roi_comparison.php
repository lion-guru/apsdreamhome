<?php
$pageTitle = $pageTitle ?? 'ROI Comparison';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$report = $report ?? ['total_investment' => 0, 'total_returns' => 0, 'avg_roi' => 0, 'period' => '1 year'];
$properties = $properties ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-chart-bar me-2 text-success"></i>ROI Comparison Report</h1>
        <a href="<?= $base ?>/admin/reports" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Investment</div><div class="h5 mb-0 fw-bold">₹<?= number_format(floatval($report['total_investment'] ?? 0), 2) ?></div></div>
                        <div class="col-auto"><i class="fas fa-rupee-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-success text-uppercase mb-1">Total Returns</div><div class="h5 mb-0 fw-bold">₹<?= number_format(floatval($report['total_returns'] ?? 0), 2) ?></div></div>
                        <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-info text-uppercase mb-1">Avg. ROI</div><div class="h5 mb-0 fw-bold"><?= number_format(floatval($report['avg_roi'] ?? 0), 2) ?>%</div></div>
                        <div class="col-auto"><i class="fas fa-percentage fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-warning text-uppercase mb-1">Period</div><div class="h5 mb-0 fw-bold"><?= htmlspecialchars($report['period'] ?? 'N/A') ?></div></div>
                        <div class="col-auto"><i class="fas fa-calendar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Property vs Investment Comparison</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($properties)): ?>
                        <p class="text-muted text-center py-4"><i class="fas fa-chart-bar fa-2x d-block mb-2"></i>No property data available for comparison.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Investment</th>
                                        <th>Current Value</th>
                                        <th>Return</th>
                                        <th>ROI %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($properties as $p): ?>
                                    <?php
                                        $investment = floatval($p['investment'] ?? $p['purchase_price'] ?? 0);
                                        $current = floatval($p['current_value'] ?? $p['market_price'] ?? 0);
                                        $return = $current - $investment;
                                        $roi = $investment > 0 ? ($return / $investment) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($p['name'] ?? $p['property_name'] ?? $p['title'] ?? '') ?></strong></td>
                                        <td>₹<?= number_format($investment, 2) ?></td>
                                        <td>₹<?= number_format($current, 2) ?></td>
                                        <td class="<?= $return >= 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format($return, 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $roi >= 0 ? 'success' : 'danger' ?>"><?= number_format($roi, 2) ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-info">Chart Preview</h6></div>
                <div class="card-body text-center py-5">
                    <canvas id="roiChart" width="300" height="300"></canvas>
                    <p class="text-muted mt-2 small">ROI distribution across properties</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('roiChart');
    if (ctx) {
        var labels = [];
        var data = [];
        var colors = [];
        <?php foreach ($properties as $p): ?>
            <?php $inv = floatval($p['investment'] ?? $p['purchase_price'] ?? 0); ?>
            <?php $cur = floatval($p['current_value'] ?? $p['market_price'] ?? 0); ?>
            <?php $r = $inv > 0 ? (($cur - $inv) / $inv) * 100 : 0; ?>
            labels.push('<?= addslashes(htmlspecialchars($p['name'] ?? $p['property_name'] ?? $p['title'] ?? '')) ?>');
            data.push(<?= number_format($r, 2) ?>);
            colors.push(<?= $r >= 0 ? "'rgba(40,167,69,0.7)'" : "'rgba(220,53,69,0.7)'" ?>);
        <?php endforeach; ?>
        new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'ROI %', data: data, backgroundColor: colors }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return v + '%'; } } } } }
        });
    }
});
</script>
