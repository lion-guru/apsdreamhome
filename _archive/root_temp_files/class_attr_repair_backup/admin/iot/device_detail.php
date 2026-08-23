ï»¿<?php
$device = $device ?? [];
$readings = $readings ?? [];
$history = $history ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-microchip me-2"></i><?= htmlspecialchars($device['name'] ?? '') ?></h2>
    <a href="<?= BASE_URL ?>/admin/iot/devices" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Devices</a>
</div>

<div class="row">
    <div class="col-lg-5 mb-3">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Device Info</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between border-bottom py-2"><span>Status</span><span class="badge bg-<?= match($device['status'] ?? 'offline') { 'online'=>'success','fault'=>'danger','configuring'=>'warning',default=>'secondary' } ?>"><?= ucfirst($device['status'] ?? 'offline') ?></span></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span>Category</span><strong><?= ucfirst($device['category'] ?? 'smart') ?></strong></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span>Location</span><strong><?= htmlspecialchars($device['location'] ?? '—') ?></strong></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span>UID</span><strong><?= htmlspecialchars($device['device_uid'] ?? '—') ?></strong></div>
                <div class="d-flex justify-content-between py-2"><span>Last Seen</span><strong><?= !empty($device['last_seen_at']) ? date('M d, H:i', strtotime($device['last_seen_at'])) : '—' ?></strong></div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h6 class="mb-0">Record Reading</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/iot/device/reading" id="readingForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="device_id" value="<?= $device['id'] ?>">
                    <div class="row">
                        <div class="col-5 mb-2"><input type="text" name="metric" class="form-control" placeholder="metric" value="temperature" required></div>
                        <div class="col-4 mb-2"><input type="number" step="0.01" name="value" class="form-control" placeholder="value" required></div>
                        <div class="col-3 mb-2"><input type="text" name="unit" class="form-control" placeholder="unit" value="Â°C"></div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Record</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-3">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Latest Readings</h6></div>
            <div class="card-body p-0">
                <?php if (empty($readings)): ?>
                    <p class="text-muted text-center py-3">No readings yet.</p>
                <?php else: ?>
                    <table class="table mb-0"><tbody>
                    <?php foreach ($readings as $r): ?>
                        <tr><td><strong><?= htmlspecialchars($r['metric'] ?? '') ?></strong></td><td class="text-end"><span class="badge bg-primary"><?= $r['value'] ?> <?= htmlspecialchars($r['unit'] ?? '') ?></span></td><td class="text-end"><small class="text-muted"><?= date('M d H:i', strtotime($r['recorded_at'])) ?></small></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h6 class="mb-0">History (last 50)</h6></div>
            <div class="card-body p-0">
                <?php if (empty($history)): ?>
                    <p class="text-muted text-center py-3">No history.</p>
                <?php else: ?>
                    <div class="table-responsive" class="style-63664">
                        <table class="table table-sm mb-0"><tbody>
                        <?php foreach ($history as $h): ?>
                            <tr><td><?= htmlspecialchars($h['metric'] ?? '') ?></td><td class="text-end"><?= $h['value'] ?> <?= htmlspecialchars($h['unit'] ?? '') ?></td><td class="text-end"><small class="text-muted"><?= date('M d H:i', strtotime($h['recorded_at'])) ?></small></td></tr>
                        <?php endforeach; ?>
                        </tbody></table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('readingForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    showLoader();
    fetch('<?= BASE_URL ?>/admin/iot/device/reading', { method:'POST', body: fd })
        .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else showToast('Failed', 'danger'); ).finally(() => hideLoader());
});
</script>
