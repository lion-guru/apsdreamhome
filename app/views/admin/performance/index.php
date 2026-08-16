<?php $page_title = $page_title ?? 'System Performance';
$phpVersion = $phpVersion ?? phpversion();
$mysqlVersion = $mysqlVersion ?? '';
$memoryUsed = $memoryUsed ?? 0;
$memoryPeak = $memoryPeak ?? 0;
$memoryLimit = $memoryLimit ?? ini_get('memory_limit');
$memoryLimitBytes = $memoryLimitBytes ?? 0;
$diskFree = $diskFree ?? 0;
$diskTotal = $diskTotal ?? 0;
$diskUsed = $diskUsed ?? 0;
$uptime = $uptime ?? '';
$serverSoftware = $serverSoftware ?? '';
$activeConn = $activeConn ?? 0;
$totalTables = $totalTables ?? 0;
$totalRows = $totalRows ?? 0;
$dbSize = $dbSize ?? 0;
$extensions = $extensions ?? [];
$loadedExtensions = $loadedExtensions ?? [];
$opcacheEnabled = $opcacheEnabled ?? false;
$opcacheHits = $opcacheHits ?? 0;
$opcacheMisses = $opcacheMisses ?? 0;
$slowQueries = $slowQueries ?? 0;
$totalQueries = $totalQueries ?? 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-tachometer-alt me-2 text-primary"></i>System Performance</h2>
        <button onclick="location.reload()" class="btn btn-outline-primary"><i class="fas fa-sync me-1"></i>Refresh</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fab fa-php"></i></span></div>
                    <div><div class="aps-cp-stat-label">PHP Version</div><div class="aps-cp-stat-value fs-5"><?= htmlspecialchars($phpVersion ?? '') ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-database"></i></span></div>
                    <div><div class="aps-cp-stat-label">MySQL Version</div><div class="aps-cp-stat-value fs-5"><?= htmlspecialchars($mysqlVersion ?? '') ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-server"></i></span></div>
                    <div><div class="aps-cp-stat-label">Server</div><div class="aps-cp-stat-value fs-6"><?= htmlspecialchars($serverSoftware ?? '') ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-plug"></i></span></div>
                    <div><div class="aps-cp-stat-label">OPcache</div><div class="aps-cp-stat-value fs-5"><?= $opcacheEnabled ? '<span class="text-success">Active</span>' : '<span class="text-danger">Off</span>' ?></div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <h6 class="text-muted mb-3"><i class="fas fa-memory me-1"></i>Memory Usage</h6>
                <?php $memPct = $memoryLimitBytes > 0 ? min(100, ($memoryUsed / $memoryLimitBytes) * 100) : 0; ?>
                <div class="mb-2"><strong><?= number_format($memoryUsed / 1048576, 1) ?></strong> MB / <?= $memoryLimit === '-1' ? 'Unlimited' : number_format($memoryLimitBytes / 1048576, 0) . ' MB' ?></div>
                <div class="progress mb-1" class="style-44570"><div class="progress-bar bg-<?= $memPct > 80 ? 'danger' : ($memPct > 50 ? 'warning' : 'success') ?>" class="style-22963"></div></div>
                <small class="text-muted">Peak: <?= number_format($memoryPeak / 1048576, 1) ?> MB</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <h6 class="text-muted mb-3"><i class="fas fa-hdd me-1"></i>Disk Usage</h6>
                <?php $diskPct = $diskTotal > 0 ? min(100, ($diskUsed / $diskTotal) * 100) : 0; ?>
                <div class="mb-2"><strong><?= number_format($diskUsed / 1073741824, 1) ?></strong> GB / <?= number_format($diskTotal / 1073741824, 1) ?> GB</div>
                <div class="progress mb-1" class="style-44570"><div class="progress-bar bg-<?= $diskPct > 90 ? 'danger' : ($diskPct > 70 ? 'warning' : 'info') ?>" class="style-45310"></div></div>
                <small class="text-muted">Free: <?= number_format($diskFree / 1073741824, 1) ?> GB</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <h6 class="text-muted mb-3"><i class="fas fa-database me-1"></i>Database Size</h6>
                <div class="mb-2"><strong><?= $dbSize ?></strong> MB</div>
                <div class="mb-1"><span class="text-muted">Tables:</span> <?= $totalTables ?></div>
                <div><span class="text-muted">Total Rows:</span> <?= number_format($totalRows) ?></div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body text-center">
                <div class="aps-cp-stat-label">Active DB Connections</div>
                <div class="aps-cp-stat-value text-primary"><?= $activeConn ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body text-center">
                <div class="aps-cp-stat-label">Total Queries</div>
                <div class="aps-cp-stat-value"><?= number_format($totalQueries) ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body text-center">
                <div class="aps-cp-stat-label">Slow Queries</div>
                <div class="aps-cp-stat-value text-<?= $slowQueries > 0 ? 'warning' : 'success' ?>"><?= number_format($slowQueries) ?></div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body text-center">
                <div class="aps-cp-stat-label">OPcache Hit Rate</div>
                <div class="aps-cp-stat-value"><?= ($opcacheHits + $opcacheMisses) > 0 ? round($opcacheHits / ($opcacheHits + $opcacheMisses) * 100, 1) . '%' : 'N/A' ?></div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-plug me-2"></i>PHP Extensions</div>
                <div class="aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0"><thead><tr><th>Extension</th><th>Status</th></tr></thead><tbody>
                        <?php foreach ($extensions as $ext): ?>
                            <tr><td><code><?= $ext ?></code></td><td><span class="aps-cp-badge badge bg-<?= in_array($ext, $loadedExtensions) ? 'success' : 'danger' ?>"><?= in_array($ext, $loadedExtensions) ? 'Loaded' : 'Missing' ?></span></td></tr>
                        <?php endforeach; ?>
                        </tbody></table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-cog me-2"></i>PHP Configuration</div>
                <div class="aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr><td>Memory Limit</td><td><strong><?= ini_get('memory_limit') ?></strong></td></tr>
                                <tr><td>Max Execution Time</td><td><strong><?= ini_get('max_execution_time') ?>s</strong></td></tr>
                                <tr><td>Upload Max Size</td><td><strong><?= ini_get('upload_max_filesize') ?></strong></td></tr>
                                <tr><td>Post Max Size</td><td><strong><?= ini_get('post_max_size') ?></strong></td></tr>
                                <tr><td>Max Input Vars</td><td><strong><?= ini_get('max_input_vars') ?></strong></td></tr>
                                <tr><td>Error Reporting</td><td><strong><?= error_reporting() === E_ALL ? 'All' : 'Custom' ?></strong></td></tr>
                                <tr><td>Display Errors</td><td><span class="aps-cp-badge badge bg-<?= ini_get('display_errors') ? 'danger' : 'success' ?>"><?= ini_get('display_errors') ? 'On' : 'Off' ?></span></td></tr>
                                <tr><td>Session Cache Limiter</td><td><strong><?= ini_get('session.cache_limiter') ?: 'None' ?></strong></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
