<?php
/**
 * AutoCAD & GeoJSON Batch Plot Layout Exchange View
 */
$colonies = $colonies ?? [];
$colony_id = $colony_id ?? 0;
$selected_colony = $selected_colony ?? null;
$stats = $stats ?? ['total_plots' => 0, 'surveyed_plots' => 0, 'synthetic_plots' => 0, 'total_sqft' => 0.0];
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-draw-polygon text-primary me-2"></i>CAD &amp; GeoJSON Plot Layout Exchange</h4>
        <p class="text-muted mb-0">Import civil surveyor AutoCAD DXF/GeoJSON boundary coordinates &amp; export live colony layouts</p>
    </div>
    <?php if ($colony_id > 0): ?>
    <div>
        <a href="<?= $base ?>/admin/plots/cad-exchange/export?colony_id=<?= (int)$colony_id ?>" class="btn btn-outline-success">
            <i class="fas fa-file-export me-1"></i> Export Colony GeoJSON
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Colony Selector -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= $base ?>/admin/plots/cad-exchange" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Select Colony / Township</label>
                <select name="colony_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Choose Colony --</option>
                    <?php foreach ($colonies as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $colony_id === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= $e($c['name']) ?> <?= !empty($c['location']) ? ' (' . $e($c['location']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-7 text-md-end pt-3">
                <span class="text-muted small">Standard RFC 7946 GeoJSON format supported with Polygon geometries.</span>
            </div>
        </form>
    </div>
</div>

<?php if ($selected_colony): ?>
<!-- Colony Layout Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <span class="text-muted small">Total Plots in Colony</span>
            <h4 class="mb-0 fw-bold"><?= number_format($stats['total_plots']) ?></h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-success border-4">
            <span class="text-muted small">Surveyor Polygon Plots</span>
            <h4 class="mb-0 fw-bold text-success"><?= number_format($stats['surveyed_plots']) ?></h4>
            <span class="small text-muted">Real CAD coordinates stored</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-warning border-4">
            <span class="text-muted small">Synthetic Grid Plots</span>
            <h4 class="mb-0 fw-bold text-warning"><?= number_format($stats['synthetic_plots']) ?></h4>
            <span class="small text-muted">Auto-placed grid fallback</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
            <span class="text-muted small">Total Colony Layout Area</span>
            <h4 class="mb-0 fw-bold"><?= number_format($stats['total_sqft']) ?> <small class="fs-6 text-muted">sqft</small></h4>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Import Form -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-file-upload text-primary me-2"></i>Batch Import Surveyor Layout</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $base ?>/admin/plots/cad-exchange/import" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="colony_id" value="<?= (int)$colony_id ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Upload GeoJSON / CAD File *</label>
                        <input type="file" name="cad_file" class="form-control" accept=".geojson,.json" required>
                        <div class="form-text small">Upload exported AutoCAD GeoJSON FeatureCollection containing plot polygons.</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Default Rate (₹/sqft)</label>
                            <input type="number" step="1" min="100" name="default_rate" class="form-control" value="1200">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Road Width Default</label>
                            <input type="number" step="1" min="10" name="default_road" class="form-control" value="30">
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="update_existing" value="1" id="updateExisting" checked>
                        <label class="form-check-label small" for="updateExisting">
                            <strong>Update existing plots</strong> if plot number already exists in this colony
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="fas fa-layer-group me-1"></i> Import &amp; Map Coordinates
                    </button>
                </form>

                <hr class="my-4">

                <div class="bg-light p-3 rounded small">
                    <div class="fw-semibold mb-1"><i class="fas fa-info-circle text-primary me-1"></i> Expected GeoJSON Properties:</div>
                    <code class="text-dark d-block">
                        {<br>
                        &nbsp;&nbsp;"plot_number": "A-101",<br>
                        &nbsp;&nbsp;"block": "A",<br>
                        &nbsp;&nbsp;"area_sqft": 1000,<br>
                        &nbsp;&nbsp;"facing": "East",<br>
                        &nbsp;&nbsp;"corner_plot": 1,<br>
                        &nbsp;&nbsp;"park_facing": 0<br>
                        }
                    </code>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Map Preview of Colony -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-map-marked-alt text-success me-2"></i>Live Colony Map Layout</h5>
                <span class="badge bg-light text-dark border"><?= $e($selected_colony['name']) ?></span>
            </div>
            <div class="card-body p-0">
                <div id="colonyMap" style="height: 480px; width: 100%; border-radius: 0 0 12px 12px;"></div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var colonyId = <?= (int)$colony_id ?>;
    var centerLat = <?= (float)($selected_colony['latitude'] ?: 26.76) ?>;
    var centerLng = <?= (float)($selected_colony['longitude'] ?: 83.37) ?>;

    var map = L.map('colonyMap').setView([centerLat, centerLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Fetch live GeoJSON from API
    fetch('<?= $base ?>/api/colony/' + colonyId + '/map/geojson')
        .then(function(res) { return res.json(); })
        .then(function(geojson) {
            if (geojson && geojson.features && geojson.features.length > 0) {
                var layer = L.geoJSON(geojson, {
                    style: function(feature) {
                        var color = feature.properties.marker_color || '#22c55e';
                        return {
                            color: '#1e293b',
                            weight: 1.5,
                            fillColor: color,
                            fillOpacity: 0.65
                        };
                    },
                    onEachFeature: function(feature, layer) {
                        var p = feature.properties;
                        var popup = '<strong>Plot ' + (p.plot_number || '') + '</strong> (Block ' + (p.block || '') + ')<br>' +
                                    'Area: ' + (p.area_sqft || 0) + ' sqft<br>' +
                                    'Status: ' + (p.status || 'available') + '<br>' +
                                    'Facing: ' + (p.facing || 'N/A');
                        layer.bindPopup(popup);
                    }
                }).addTo(map);

                try {
                    map.fitBounds(layer.getBounds(), { padding: [30, 30] });
                } catch(e) {}
            }
        })
        .catch(function(err) {
            console.error('Failed to load colony geojson:', err);
        });
});
</script>
<?php endif; ?>
