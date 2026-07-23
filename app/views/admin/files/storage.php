<?php
$pageTitle = $pageTitle ?? 'Storage Dashboard';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$storage_stats = $storage_stats ?? ['used_space' => 0, 'total_space' => 0, 'file_count' => 0, 'by_category' => [], 'used_percent' => 0];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-database me-2 text-info"></i>Storage Dashboard</h1>
        <a href="<?= $base ?>/admin/files" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-primary text-uppercase mb-1">Used Space</div><div class="h5 mb-0 fw-bold"><?= $storage_stats['used_space_human'] ?? (function($b){ return $b >= 1073741824 ? number_format($b/1073741824,2).' GB' : ($b >= 1048576 ? number_format($b/1048576,2).' MB' : number_format($b).' B'); })(floatval($storage_stats['used_space'] ?? 0)) ?></div></div>
                        <div class="col-auto"><i class="fas fa-hdd fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-success text-uppercase mb-1">Total Space</div><div class="h5 mb-0 fw-bold"><?= $storage_stats['total_space_human'] ?? (function($b){ return $b >= 1073741824 ? number_format($b/1073741824,2).' GB' : number_format($b).' B'; })(floatval($storage_stats['total_space'] ?? 0)) ?></div></div>
                        <div class="col-auto"><i class="fas fa-server fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-info text-uppercase mb-1">File Count</div><div class="h5 mb-0 fw-bold"><?= number_format($storage_stats['file_count'] ?? 0) ?></div></div>
                        <div class="col-auto"><i class="fas fa-files fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-warning text-uppercase mb-1">Used %</div><div class="h5 mb-0 fw-bold"><?= number_format($storage_stats['used_percent'] ?? 0, 1) ?>%</div></div>
                        <div class="col-auto"><i class="fas fa-chart-pie fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Storage Usage</h6></div>
                <div class="card-body text-center">
                    <?php $pct = min(floatval($storage_stats['used_percent'] ?? 0), 100); ?>
                    <div class="position-relative d-inline-block">
                        <canvas id="storageChart" width="200" height="200"></canvas>
                        <div class="mt-3">
                            <div class="progress" style="height:30px">
                                <div class="progress-bar bg-<?= $pct > 90 ? 'danger' : ($pct > 70 ? 'warning' : 'success') ?>" role="progressbar" style="width:<?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"><?= number_format($pct, 1) ?>%</div>
                            </div>
                            <small class="text-muted">of available storage used</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">By Category</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php $byCategory = $storage_stats['by_category'] ?? []; ?>
                    <?php if (empty($byCategory)): ?>
                        <p class="text-muted text-center py-3">No category data available.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Category</th><th>Files</th><th>Size</th></tr></thead>
                                <tbody>
                                    <?php foreach ($byCategory as $cat): ?>
                                    <tr>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($cat['category'] ?? $cat['name'] ?? '') ?></span></td>
                                        <td><?= number_format(intval($cat['count'] ?? 0)) ?></td>
                                        <td><?= htmlspecialchars($cat['size_human'] ?? (function($b){ return $b >= 1048576 ? number_format($b/1048576,1).' MB' : number_format($b).' B'; })(floatval($cat['size'] ?? 0))) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('storageChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Used', 'Free'],
                datasets: [{
                    data: [<?= $pct ?>, <?= 100 - $pct ?>],
                    backgroundColor: ['<?= $pct > 90 ? "#dc3545" : ($pct > 70 ? "#ffc107" : "#28a745") ?>', '#e9ecef']
                }]
            },
            options: { cutout: '70%', plugins: { tooltip: { callbacks: { label: function(ctx) { return ctx.parsed + '%'; } } } } }
        });
    }
});
</script>
