<?php
$providers = $providers ?? [];
$defaultProvider = $defaultProvider ?? 'none';
$fileCount = $fileCount ?? 0;
$totalSize = $totalSize ?? 0;
$byPrefix = $byPrefix ?? [];
$byType = $byType ?? [];
$storageCost = $storageCost ?? 0;
$requestCost = $requestCost ?? 0;
$totalCost = $totalCost ?? 0;
$lastBackup = $lastBackup ?? null;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Storage Analytics & Health</h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-info" onclick="verifyBackup()"><i class="fas fa-shield-alt me-2"></i>Verify Backup</button>
            <a href="<?= BASE_URL . '/admin/cloud-storage' ?>" class="btn btn-primary"><i class="fas fa-cloud me-2"></i>File Manager</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-2"><div class="card bg-primary text-white"><div class="card-body"><h3><?= count($providers) ?></h3><small>Providers</small></div></div></div>
        <div class="col-md-2"><div class="card bg-success text-white"><div class="card-body"><h3><?= number_format($fileCount) ?></h3><small>Files</small></div></div></div>
        <div class="col-md-2"><div class="card bg-info text-white"><div class="card-body"><h3><?= number_format($totalSize / 1024 / 1024, 1) ?> MB</h3><small>Total Size</small></div></div></div>
        <div class="col-md-2"><div class="card bg-warning text-dark"><div class="card-body"><h3>₹<?= number_format($totalCost * 75, 0) ?></h3><small>Est. Monthly Cost</small></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><h3><?= $defaultProvider ?></h3><small>Default Provider</small></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body"><h3><?= $lastBackup ? date('M d', strtotime($lastBackup)) : '—' ?></h3><small>Last Backup</small></div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card"><div class="card-header fw-bold">Size by Folder</div>
                <div class="card-body p-0"><table class="table mb-0">
                    <thead><tr><th>Folder</th><th class="text-end">Size</th></tr></thead>
                    <tbody>
                    <?php foreach ($byPrefix as $p => $sz): ?>
                        <tr><td><?= htmlspecialchars($p) ?></td><td class="text-end"><?= number_format($sz / 1024, 1) ?> KB</td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($byPrefix)): ?><tr><td colspan="2" class="text-muted text-center">No data</td></tr><?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-header fw-bold">Size by File Type</div>
                <div class="card-body p-0"><table class="table mb-0">
                    <thead><tr><th>Type</th><th class="text-end">Size</th></tr></thead>
                    <tbody>
                    <?php foreach ($byType as $t => $sz): ?>
                        <tr><td><?= htmlspecialchars($t) ?></td><td class="text-end"><?= number_format($sz / 1024, 1) ?> KB</td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($byType)): ?><tr><td colspan="2" class="text-muted text-center">No data</td></tr><?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>

    <div class="card mb-4"><div class="card-header fw-bold">Cost Calculator</div>
        <div class="card-body">
            <form onsubmit="return calcCost(event)" class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label small">Size (GB)</label><input type="number" id="cc_size" class="form-control" step="0.1" min="0" value="10"></div>
                <div class="col-md-2"><label class="form-label small">Requests/month</label><input type="number" id="cc_req" class="form-control" min="0" value="10000"></div>
                <div class="col-md-2"><label class="form-label small">Tier</label><select id="cc_tier" class="form-select"><option value="standard">Standard</option><option value="infrequent">Infrequent</option><option value="glacier">Glacier</option><option value="deep_archive">Deep Archive</option></select></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Calculate</button></div>
                <div class="col-md-4"><div id="cc_result" class="alert alert-info py-2 mb-0">Enter values and click Calculate</div></div>
            </form>
        </div>
    </div>

    <div class="card"><div class="card-header fw-bold">Presigned URL Generator</div>
        <div class="card-body">
            <form onsubmit="return genUrl(event)" class="row g-2 align-items-end">
                <div class="col-md-5"><label class="form-label small">File Path</label><input type="text" id="pg_path" class="form-control" placeholder="documents/report.pdf"></div>
                <div class="col-md-2"><label class="form-label small">Expiry (minutes)</label><input type="number" id="pg_expiry" class="form-control" min="1" max="10080" value="60"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Generate</button></div>
                <div class="col-md-3"><div id="pg_result" class="text-muted small">Generated URL will appear here</div></div>
            </form>
        </div>
    </div>
</div>

<script>
function calcCost(e) {
    e.preventDefault();
    const size = document.getElementById('cc_size').value;
    const req = document.getElementById('cc_req').value;
    const tier = document.getElementById('cc_tier').value;
    fetch('<?= BASE_URL . '/admin/storage-analytics/cost' ?>?size_gb=' + size + '&requests=' + req + '&tier=' + tier)
        .then(r => r.json()).then(d => {
            if (d.success) {
                document.getElementById('cc_result').innerHTML = 'Storage: $' + d.storage_cost.toFixed(2) + ' + Requests: $' + d.request_cost.toFixed(4) + ' + Egress: $' + d.egress_cost.toFixed(2) + ' = <strong>$' + d.total_monthly.toFixed(2) + '/month</strong>';
            }
        });
    return false;
}
function genUrl(e) {
    e.preventDefault();
    const path = document.getElementById('pg_path').value;
    const expiry = document.getElementById('pg_expiry').value;
    fetch('<?= BASE_URL . '/admin/storage-analytics/presigned' ?>?path=' + encodeURIComponent(path) + '&expiry=' + expiry)
        .then(r => r.json()).then(d => {
            if (d.success) {
                document.getElementById('pg_result').innerHTML = '<a href="' + d.url + '" target="_blank">Download Link</a> (expires in ' + d.expires_minutes + ' min)';
            } else {
                document.getElementById('pg_result').textContent = d.error || 'Failed';
            }
        });
    return false;
}
function verifyBackup() {
    if (!confirm('Verify a backup file from S3?')) return;
    fetch('<?= BASE_URL . '/admin/storage-analytics/backup-verify' ?>', {
        method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest'},
        body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    }).then(r => r.json()).then(d => {
        if (d.success) { alert('Backup verified: ' + d.path + ' (' + d.size_bytes + ' bytes, md5=' + d.checksum + ')'); }
        else { alert(d.error || 'Verify failed'); }
    });
}
</script>
