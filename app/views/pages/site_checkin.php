<?php
/** GPS geo-checkin for a site visit. */
/** @var array $visit */
$visit = $visit ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$flash_ok = $_SESSION['flash_success'] ?? '';
$flash_err = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="container py-4" style="max-width:640px">
    <h4 class="mb-1"><i class="fas fa-location-crosshairs me-2"></i>Site Visit Check-in</h4>
    <p class="text-muted">Visit #<?= (int)($visit['id'] ?? 0) ?> · <?= $e($visit['colony_name'] ?? '') ?> · <?= $e($visit['visit_date'] ?? '') ?> · status: <strong><?= $e($visit['status'] ?? '') ?></strong></p>
    <?php if ($flash_ok !== ''): ?><div class="alert alert-success"><?= $e($flash_ok) ?></div><?php endif; ?>
    <?php if ($flash_err !== ''): ?><div class="alert alert-danger"><?= $e($flash_err) ?></div><?php endif; ?>
    <?php if (!empty($visit['checkin_time'])): ?>
        <div class="alert alert-info">Last check-in: <?= $e($visit['checkin_time']) ?> at <?= $e($visit['checkin_lat']) ?>, <?= $e($visit['checkin_lng']) ?></div>
    <?php endif; ?>
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="<?= $base ?>/site-visit/checkin/<?= (int)($visit['id'] ?? 0) ?>/submit" enctype="multipart/form-data" id="checkinForm">
            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="lat" id="ckLat">
            <input type="hidden" name="lng" id="ckLng">
            <div class="mb-2"><span id="gpsStatus" class="badge bg-secondary">Locating… allow GPS access</span></div>
            <div class="mb-3"><label class="form-label small">Gate selfie (optional, ≤5MB)</label><input type="file" name="gate_photo" class="form-control" accept=".jpg,.jpeg,.png,.webp"></div>
            <button type="submit" class="btn btn-success w-100" id="ckBtn" disabled><i class="fas fa-check me-1"></i>Check In at Site</button>
        </form>
    </div></div>
</div>
<script>
(function () {
    var lat = document.getElementById('ckLat'), lng = document.getElementById('ckLng');
    var st = document.getElementById('gpsStatus'), btn = document.getElementById('ckBtn');
    if (!('geolocation' in navigator)) {
        st.textContent = 'GPS not supported on this device';
        st.className = 'badge bg-danger';
        return;
    }
    navigator.geolocation.getCurrentPosition(function (pos) {
        lat.value = pos.coords.latitude.toFixed(7);
        lng.value = pos.coords.longitude.toFixed(7);
        st.textContent = 'Located: ' + lat.value + ', ' + lng.value + ' (±' + Math.round(pos.coords.accuracy) + 'm)';
        st.className = 'badge bg-success';
        btn.disabled = false;
    }, function (err) {
        st.textContent = 'Location blocked — enable GPS to check in';
        st.className = 'badge bg-danger';
    }, { enableHighAccuracy: true, timeout: 20000 });
})();
</script>
