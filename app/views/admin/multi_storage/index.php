<?php
$providers = $providers ?? [];
$health = $health ?? [];
$activeProvider = $activeProvider ?? null;
$providerNames = $providerNames ?? [];
$driverLabels = [
    'aws_s3' => 'AWS S3',
    'gcs' => 'Google Cloud Storage',
    'local' => 'Local Filesystem',
    'backblaze_b2' => 'Backblaze B2',
    'cloudflare_r2' => 'Cloudflare R2',
    'azure' => 'Azure Blob Storage',
];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Multi-Provider Storage Management</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="runHealthCheck()"><i class="fas fa-sync-alt me-2"></i>Run Health Check</button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= count($providers) ?></h3><small>Configured Providers</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3><?= count(array_filter($health, fn($h) => $h['healthy'])) ?></h3><small>Healthy</small></div></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body"><h3><?= count(array_filter($health, fn($h) => !$h['healthy'])) ?></h3><small>Degraded</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h3><?= $activeProvider ? $driverLabels[$activeProvider] ?? $activeProvider : 'None' ?></h3><small>Active Provider</small></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-bold">Provider Status Overview</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Provider</th><th>Status</th><th>Latency</th><th>Failures</th><th>Last Check</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($providerNames as $name): ?>
                    <?php $h = $health[$name] ?? ['healthy' => false, 'latency_ms' => 'N/A', 'consecutive_failures' => 0, 'last_check' => 'Never', 'last_error' => 'Not checked']; ?>
                    <tr class="<?= $h['healthy'] ? '' : 'table-danger' ?>">
                        <td>
                            <strong><?= ucfirst(str_replace('_', ' ', $name)) ?></strong>
                            <?php if ($activeProvider === $name): ?><span class="badge bg-success ms-2">ACTIVE</span><?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $health[$name]['healthy'] ?? false ? 'success' : 'danger' ?>">
                                <?= $health[$name]['healthy'] ? 'Healthy' : 'Degraded' ?>
                            </span>
                        </td>
                        <td><?= $health[$name]['latency_ms'] ?? 'N/A' ?> ms</td>
                        <td>
                            <span class="badge bg-<?= ($health[$name]['consecutive_failures'] ?? 0) > 0 ? 'danger' : 'success' ?>">
                                <?= $health[$name]['consecutive_failures'] ?? 0 ?>
                            </span>
                        </td>
                        <td><?= $health[$name]['last_check'] ?? 'Never' ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="checkProvider('<?= $name ?>')"><i class="fas fa-sync"></i></button>
                                <button class="btn btn-outline-warning" onclick="forceFailover('<?= $name ?>')" <?= $activeProvider === $name ? 'disabled' : '' ?>><i class="fas fa-exchange-alt"></i> Failover</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-bold">Provider Configuration</div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($providers as $provider): ?>
                    <?php $name = $provider->getDriver(); $driverName = $driverLabels[$name] ?? ucfirst($name); ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-<?= str_contains($name, 's3') ? 'cloud' : ($name === 'gcs' ? 'cloud' : ($name === 'local' ? 'hdd' : 'database')) ?> me-2"></i><?= $driverLabels[$name] ?? ucfirst($name) ?></span>
                                <span class="badge bg-<?= $health[$name]['healthy'] ?? false ? 'success' : 'danger' ?>"><?= $health[$name]['healthy'] ? 'Online' : 'Offline' ?></span>
                            </div>
                            <div class="card-body">
                                <small class="text-muted">Latency: <?= $health[$name]['latency_ms'] ?? 'N/A' ?> ms</small><br>
                                <small class="text-muted">Failures: <?= $health[$name]['consecutive_failures'] ?? 0 ?></small><br>
                                <small class="text-muted">Last: <?= $health[$name]['last_check'] ?? 'Never' ?></small>
                                <div class="mt-2 d-flex gap-1">
                                    <a href="<?= BASE_URL . '/admin/multi-storage/configure/' . $name ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-cog me-1"></i>Configure</a>
                                    <button class="btn btn-sm btn-outline-info" onclick="checkProvider('<?= $name ?>')"><i class="fas fa-sync-alt me-1"></i>Check</button>
                                    <?php if ($activeProvider !== $name && ($health[$name]['healthy'] ?? false)): ?>
                                        <button class="btn btn-sm btn-outline-warning" onclick="forceFailover('<?= $name ?>')"><i class="fas fa-exchange-alt me-1"></i>Failover</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function checkProvider(provider) {
    const btn = event.target.closest('button');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
    fetch('<?= BASE_URL . '/admin/multi-storage/health-check' ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    }).then(r => r.json()).then(d => {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-sync"></i>'; }
        if (d.success) {
            location.reload();
        } else {
            alert(d.error || 'Health check failed');
        }
    }).catch(() => { if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-sync"></i>'; } alert('Health check failed'); });
}

function forceFailover(target) {
    if (!confirm('Failover to ' + target + '? This will switch the active storage provider.')) return;
    fetch('<?= BASE_URL . '/admin/multi-storage/failover' ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>&target=' + encodeURIComponent(target)
    }).then(r => r.json()).then(d => {
        if (d.success) {
            alert(d.message);
            location.reload();
        } else {
            alert(d.error || 'Failover failed');
        }
    }).catch(() => alert('Failover request failed'));
}
</script>