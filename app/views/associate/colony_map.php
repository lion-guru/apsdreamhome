<?php $colony = $colony ?? []; $plots = $plots ?? []; ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.min.css" />
<style>
#plotMap { height: 65vh; width: 100%; border-radius: 12px; border: 1px solid #e2e8f0; z-index: 0; }
.plot-popup { min-width: 200px; }
.plot-popup h6 { margin-bottom: 8px; font-weight: 600; font-size: 14px; }
.plot-popup .info-row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 13px; }
.plot-popup .info-row .label { color: #64748b; }
.plot-popup .info-row .value { font-weight: 500; }
.map-filter-bar { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; align-items: center; }
.map-filter-bar .btn { font-size: 13px; padding: 4px 16px; border-radius: 20px; cursor: pointer; background: #f1f5f9; border: 1px solid #e2e8f0; }
.map-filter-bar .btn.active { background: #0d9488; color: white; border-color: #0d9488; }
.map-stats-bar { background: white; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; display: flex; gap: 20px; flex-wrap: wrap; border: 1px solid #e2e8f0; }
.map-stat { display: flex; align-items: center; gap: 6px; font-size: 13px; }
.map-stat .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
</style>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fas fa-map-marked-alt" class="style-5793"></i> <?= __('assoc_cm_title', [], 'Plot Map') ?> à¢€—� <?= htmlspecialchars($colony['name'] ?? '') ?></h4>
        <span class="text-muted small"><?= count($plots) ?> <?= __('assoc_cm_plots', [], 'plots') ?> à‚Â· <?= htmlspecialchars($colony['district_name'] ?? '') ?></span>
    </div>
    <a href="<?= BASE_URL ?>/associate/browse" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i><?= __('assoc_cm_back', [], 'Back') ?></a>
</div>
<div class="map-stats-bar" id="mapStats">
    <span class="map-stat" data-status="available"><span class="dot" class="style-26706"></span> <?= __('assoc_cm_available', [], 'Available') ?>: <strong id="statAvail">0</strong></span>
    <span class="map-stat" data-status="booked"><span class="dot" class="style-4960"></span> <?= __('assoc_cm_booked', [], 'Booked') ?>: <strong id="statBooked">0</strong></span>
    <span class="map-stat" data-status="sold"><span class="dot" class="style-68656"></span> <?= __('assoc_cm_sold', [], 'Sold') ?>: <strong id="statSold">0</strong></span>
    <span class="map-stat" data-status="hold"><span class="dot" class="style-99107"></span> <?= __('assoc_cm_hold', [], 'Hold') ?>: <strong id="statHold">0</strong></span>
    <span class="map-stat ms-auto text-muted"><?= __('assoc_cm_total_value', [], 'Total Value') ?>: <strong id="statValue">à¢—šÂ¹0</strong></span>
</div>
<div class="map-filter-bar mb-3">
    <button class="btn btn-sm active" data-filter="all"><?= __('assoc_cm_all', [], 'All') ?></button>
    <button class="btn btn-sm" data-filter="available" class="style-82740"><?= __('assoc_cm_available', [], 'Available') ?></button>
    <button class="btn btn-sm" data-filter="booked" class="style-67064"><?= __('assoc_cm_booked', [], 'Booked') ?></button>
    <button class="btn btn-sm" data-filter="sold" class="style-51061"><?= __('assoc_cm_sold', [], 'Sold') ?></button>
    <button class="btn btn-sm" data-filter="hold" class="style-79191"><?= __('assoc_cm_hold', [], 'Hold') ?></button>
</div>
<div id="plotMap"></div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    var colonyId = <?= (int)($colony['id'] ?? 0) ?>;
    var baseUrl = '<?= BASE_URL ?>';
    var map = L.map('plotMap', { zoomControl: true, attributionControl: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    var geojsonLayer = null;
    var legend = L.control({ position: 'bottomright' });
    legend.onAdd = function() {
        var div = L.DomUtil.create('div', 'legend');
        div.innerHTML = '<div class="style-45261">' +
            '<div><span class="style-96563"></span><?= __('assoc_cm_available', [], 'Available') ?></div>' +
            '<div><span class="style-42723"></span><?= __('assoc_cm_booked', [], 'Booked') ?></div>' +
            '<div><span class="style-62460"></span><?= __('assoc_cm_sold', [], 'Sold') ?></div>' +
            '<div><span class="style-60540"></span><?= __('assoc_cm_hold', [], 'Hold') ?></div></div>';
        return div;
    };
    legend.addTo(map);
    function loadGeoJson() {
        fetch(baseUrl + '/api/colony/' + colonyId + '/map/geojson')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (geojsonLayer) { map.removeLayer(geojsonLayer); }
                var statusLabels = { available: '<?= __('assoc_cm_available', [], 'Available') ?>', booked: '<?= __('assoc_cm_booked', [], 'Booked') ?>', sold: '<?= __('assoc_cm_sold', [], 'Sold') ?>', hold: '<?= __('assoc_cm_hold', [], 'On Hold') ?>', reserved: '<?= __('assoc_cm_reserved', [], 'Reserved') ?>' };
                geojsonLayer = L.geoJSON(data, {
                    style: function(f) {
                        var colors = { available: '#22c55e', booked: '#eab308', sold: '#ef4444', hold: '#6b7280', reserved: '#f97316' };
                        return { fillColor: colors[f.properties.status] || '#94a3b8', color: '#1e293b', weight: 1, fillOpacity: 0.7 };
                    },
                    onEachFeature: function(f, layer) {
                        var p = f.properties;
                        var html = '<div class="plot-popup"><h6><?= __('assoc_cm_plot', [], 'Plot') ?> #' + p.plot_number +
                            ' <span class="badge bg-' + (p.status === 'available' ? 'success' : p.status === 'booked' ? 'warning text-dark' : p.status === 'sold' ? 'danger' : 'secondary') +
                            '">' + (statusLabels[p.status] || p.status) + '</span></h6>' +
                            '<div class="info-row"><span class="label"><?= __('assoc_cm_block', [], 'Block') ?></span><span class="value">' + (p.block || '-') + '</span></div>' +
                            '<div class="info-row"><span class="label"><?= __('assoc_cm_area', [], 'Area') ?></span><span class="value">' + (p.area_sqft || 0) + ' sqft</span></div>' +
                            '<div class="info-row"><span class="label"><?= __('assoc_cm_size', [], 'Size') ?></span><span class="value">' + (p.width_ft || '-') + 'x' + (p.length_ft || '-') + '</span></div>' +
                            (p.price_per_sqft ? '<div class="info-row"><span class="label"><?= __('assoc_cm_rate', [], 'Rate') ?></span><span class="value">à¢—šÂ¹' + Number(p.price_per_sqft).toLocaleString() + '/sqft</span></div>' : '') +
                            (p.total_price ? '<div class="info-row"><span class="label"><?= __('assoc_cm_price', [], 'Price') ?></span><span class="value fw-bold" class="style-5793">à¢—šÂ¹' + Number(p.total_price).toLocaleString() + '</span></div>' : '') +
                            (p.corner_plot ? '<div class="info-row"><span class="label"><?= __('assoc_cm_corner', [], 'Corner Plot') ?></span><span class="value" class="style-82740">à¢Å"®</span></div>' : '') +
                            (p.park_facing ? '<div class="info-row"><span class="label"><?= __('assoc_cm_park', [], 'Park Facing') ?></span><span class="value" class="style-82740">à¢Å"®</span></div>' : '') +
                            '<hr class="my-2"><a href="' + baseUrl + '/associate/browse" class="btn btn-sm btn-outline-primary w-100"><?= __('assoc_cm_share', [], 'Share with Customer') ?></a></div>';
                        layer.bindPopup(html, { maxWidth: 300 });
                        layer.on('mouseover', function() { this.setStyle({ fillOpacity: 0.95, weight: 2 }); });
                        layer.on('mouseout', function() { if (geojsonLayer) geojsonLayer.resetStyle(this); });
                    }
                }).addTo(map);
                if (data.features && data.features.length) {
                    map.fitBounds(geojsonLayer.getBounds(), { padding: [30, 30], maxZoom: 18 });
                } else { map.setView([26.76, 83.37], 13); }
                updateStats(data.features || []);
            });
    }
    function updateStats(features) {
        var byStatus = {}; var totalValue = 0;
        features.forEach(function(f) {
            var s = f.properties.status;
            byStatus[s] = (byStatus[s] || 0) + 1;
            if (f.properties.total_price) totalValue += Number(f.properties.total_price);
        });
        document.getElementById('statAvail').textContent = (byStatus['available'] || 0);
        document.getElementById('statBooked').textContent = (byStatus['booked'] || 0);
        document.getElementById('statSold').textContent = (byStatus['sold'] || 0);
        document.getElementById('statHold').textContent = (byStatus['hold'] || 0) + (byStatus['reserved'] || 0);
        document.getElementById('statValue').textContent = 'à¢—šÂ¹' + totalValue.toLocaleString();
    }
    document.querySelectorAll('.map-filter-bar .btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.map-filter-bar .btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var filter = this.getAttribute('data-filter');
            if (!geojsonLayer) return;
            geojsonLayer.eachLayer(function(layer) {
                var s = layer.feature.properties.status;
                var match = filter === 'all' || s === filter || (filter === 'hold' && (s === 'hold' || s === 'reserved'));
                if (layer._path) layer._path.style.display = match ? '' : 'none';
            });
        });
    });
    loadGeoJson();
})();
</script>
