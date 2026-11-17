<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-map-marked-alt text-primary me-2"></i>GPS Work Zones — Location Manager</h1>
            <div class="text-muted small mt-1">Office geofence &amp; field sites for attendance punch. Edit coordinates here, or capture them standing at the site from any browser &mdash; changes apply instantly to the mobile app.</div>
        </div>
        <a href="<?= BASE_URL ?>/admin/hrm/check_in_monitor" class="btn btn-outline-secondary"><i class="fas fa-radar me-1"></i>Check-In Monitor</a>
    </div>

    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($flash_error) ?></div>
    <?php endif; ?>

    <!-- Hidden toggle forms (outside table-valid markup) -->
    <?php foreach ($zones as $z): ?>
        <form id="zone-toggle-<?= (int) $z['id'] ?>" method="post" action="<?= BASE_URL ?>/admin/hrm/work-zones/toggle" class="d-none">
            <?= \App\Helpers\SecurityHelper::csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) $z['id'] ?>">
            <input type="hidden" name="active" value="<?= $z['is_active'] ? '0' : '1' ?>">
        </form>
    <?php endforeach; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-2"><strong class="text-gray-800">Add New Zone</strong></div>
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/hrm/work-zones/store" class="row g-2 align-items-end">
                <?= \App\Helpers\SecurityHelper::csrfField() ?>
                <div class="col-sm-4 col-md-3">
                    <label class="form-label small fw-bold" for="zone_name">Name</label>
                    <input id="zone_name" name="name" class="form-control" required maxlength="150" placeholder="e.g. Kunraghat Office">
                </div>
                <div class="col-sm-3 col-md-2">
                    <label class="form-label small fw-bold" for="zone_type">Type</label>
                    <select id="zone_type" name="type" class="form-select">
                        <option value="office">Office</option>
                        <option value="site" selected>Field Site</option>
                        <option value="client_site">Client Site</option>
                    </select>
                </div>
                <div class="col-sm-3 col-md-2">
                    <label class="form-label small fw-bold" for="zone_lat">Latitude</label>
                    <input id="zone_lat" name="latitude" type="number" step="any" class="form-control" required placeholder="26.840224">
                </div>
                <div class="col-sm-3 col-md-2">
                    <label class="form-label small fw-bold" for="zone_lng">Longitude</label>
                    <input id="zone_lng" name="longitude" type="number" step="any" class="form-control" required placeholder="83.301225">
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label small fw-bold" for="zone_radius">Radius m</label>
                    <input id="zone_radius" name="radius_m" type="number" min="10" max="10000" class="form-control" value="150" required>
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label small fw-bold" for="zone_selfie">Selfie?</label>
                    <select id="zone_selfie" name="selfie_required" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="button" class="btn btn-outline-primary flex-fill" onclick="apsUseGps(this.form.latitude, this.form.longitude)" title="Fill lat/lng from this browser's GPS" aria-label="Use current location"><i class="fas fa-crosshairs"></i></button>
                    <button type="submit" class="btn btn-primary flex-fill" aria-label="Add zone"><i class="fas fa-plus"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-2">
            <strong class="text-gray-800">Zones (<?= count($zones) ?>)</strong>
            <span class="text-muted small float-end">Office lat/lng lives in these rows — not in code</span>
        </div>
        <div class="card-body">
            <?php foreach ($zones as $z): $fid = 'zone-' . (int) $z['id']; ?>
                <div class="border rounded-3 p-3 mb-3 bg-light">
                    <form id="<?= $fid ?>" method="post" action="<?= BASE_URL ?>/admin/hrm/work-zones/update">
                        <?= \App\Helpers\SecurityHelper::csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int) $z['id'] ?>">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1" for="name-<?= (int) $z['id'] ?>">Name</label>
                                <input id="name-<?= (int) $z['id'] ?>" name="name" class="form-control form-control-sm" required maxlength="150" value="<?= htmlspecialchars($z['name']) ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold mb-1" for="type-<?= (int) $z['id'] ?>">Type</label>
                                <select id="type-<?= (int) $z['id'] ?>" name="type" class="form-select form-select-sm">
                                    <option value="office" <?= $z['type'] === 'office' ? 'selected' : '' ?>>Office</option>
                                    <option value="site" <?= $z['type'] === 'site' ? 'selected' : '' ?>>Site</option>
                                    <option value="client_site" <?= $z['type'] === 'client_site' ? 'selected' : '' ?>>Client Site</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold mb-1" for="lat-<?= (int) $z['id'] ?>">Latitude</label>
                                <input id="lat-<?= (int) $z['id'] ?>" name="latitude" type="number" step="any" class="form-control form-control-sm" value="<?= htmlspecialchars($z['latitude']) ?>" required>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small fw-bold mb-1" for="lng-<?= (int) $z['id'] ?>">Longitude</label>
                                <input id="lng-<?= (int) $z['id'] ?>" name="longitude" type="number" step="any" class="form-control form-control-sm" value="<?= htmlspecialchars($z['longitude']) ?>" required>
                            </div>
                            <div class="col-6 col-md-1">
                                <label class="form-label small fw-bold mb-1" for="radius-<?= (int) $z['id'] ?>">Radius m</label>
                                <input id="radius-<?= (int) $z['id'] ?>" name="radius_m" type="number" min="10" max="10000" class="form-control form-control-sm" value="<?= (int) $z['radius_m'] ?>" required>
                            </div>
                            <div class="col-6 col-md-1">
                                <label class="form-label small fw-bold mb-1" for="selfie-<?= (int) $z['id'] ?>">Selfie</label>
                                <select id="selfie-<?= (int) $z['id'] ?>" name="selfie_required" class="form-select form-select-sm">
                                    <option value="0" <?= !$z['selfie_required'] ? 'selected' : '' ?>>No</option>
                                    <option value="1" <?= $z['selfie_required'] ? 'selected' : '' ?>>Yes</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-1">
                                <label class="form-label small fw-bold mb-1">Status</label>
                                <div class="pt-1"><?php if ($z['is_active']): ?><span class="badge bg-success">Live</span><?php else: ?><span class="badge bg-secondary">Off</span><?php endif; ?></div>
                            </div>
                            <div class="col-12 col-md-1 d-flex gap-1">
                                <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Save changes" aria-label="Save zone <?= (int) $z['id'] ?>"><i class="fas fa-save"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="apsUseGps(this.form.latitude, this.form.longitude)" title="Fill lat/lng from this browser's GPS" aria-label="Use current location for zone <?= (int) $z['id'] ?>"><i class="fas fa-crosshairs"></i></button>
                                <button type="submit" form="zone-toggle-<?= (int) $z['id'] ?>" class="btn btn-sm btn-outline-<?= $z['is_active'] ? 'secondary' : 'success' ?> flex-fill" title="<?= $z['is_active'] ? 'Disable' : 'Enable' ?> zone" aria-label="<?= $z['is_active'] ? 'Disable' : 'Enable' ?> zone <?= (int) $z['id'] ?>"><i class="fas fa-power-off"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
            <?php if (empty($zones)): ?>
                <div class="text-center text-muted py-4">No zones configured. Add the office zone above — the app uses it for punch-in geofencing.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script nonce="<?= htmlspecialchars($GLOBALS['csp_nonce'] ?? '') ?>">
function apsUseGps(latEl, lngEl) {
    if (!navigator.geolocation) { alert('This browser does not support geolocation. Enter coordinates manually.'); return; }
    navigator.geolocation.getCurrentPosition(function (pos) {
        latEl.value = pos.coords.latitude.toFixed(7);
        lngEl.value = pos.coords.longitude.toFixed(7);
    }, function (err) {
        alert('Could not read browser GPS: ' + (err.message || 'permission denied'));
    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
}
</script>
