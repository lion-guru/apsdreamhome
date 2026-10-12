<?php $colony = $colony ?? []; $plots = $plots ?? []; $refCode = $ref_code ?? ''; $isLive = !empty($is_live_layout); $price_revision = $price_revision ?? null; ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.min.css" />
<style>
#plotMap { height: 65vh; width: 100%; border-radius: 12px; border: 1px solid #e2e8f0; z-index: 0; }
.plot-popup { min-width: 200px; font-family: 'Poppins', sans-serif; }
.plot-popup h6 { margin-bottom: 8px; font-weight: 600; font-size: 14px; }
.plot-popup .info-row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 13px; }
.plot-popup .info-row .label { color: #64748b; }
.plot-popup .info-row .value { font-weight: 500; }
@keyframes plotGlow { 0%,100% { stroke-opacity: .25; stroke-width: 2; } 50% { stroke-opacity: 1; stroke-width: 5; } }
.plot-target-pulse { stroke: #f59e0b !important; animation: plotGlow 1.5s infinite; }
.map-filter-bar { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; align-items: center; }
.map-filter-bar .btn { font-size: 13px; padding: 4px 16px; border-radius: 20px; cursor: pointer; background: #f1f5f9; border: 1px solid #e2e8f0; }
.map-filter-bar .btn.active { background: #0d9488; color: white; border-color: #0d9488; }
.map-stats-bar { background: white; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; display: flex; gap: 20px; flex-wrap: wrap; border: 1px solid #e2e8f0; }
.map-stat { display: flex; align-items: center; gap: 6px; font-size: 13px; }
.map-stat .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
.map-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px; }
.map-header h4 { margin: 0; font-size: 1.25rem; }
@media (max-width: 576px) {
    #plotMap { height: 50vh; }
    .map-stats-bar { gap: 10px; font-size: 12px; }
    .map-stat { font-size: 12px; }
}
</style>
<div class="container py-3">
    <div class="map-header">
        <div>
            <h4><i class="fas fa-map-marked-alt"></i> <?= htmlspecialchars($colony['name'] ?? '') ?> — Plot Map</h4>
            <small class="text-muted"><?= count($plots) ?> plots &middot; <?= htmlspecialchars($colony['district_name'] ?? '') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($colony['slug'] ?? '') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Colony
        </a>
    </div>
    <?php if ($refCode !== ''): ?>
    <div class="alert alert-success d-flex align-items-center py-2 mb-3" role="alert">
        <i class="fas fa-user-check me-2"></i>
        <div class="small">Shared by associate <strong><?= htmlspecialchars($refCode) ?></strong> — your hold request carries their referral automatically.</div>
    </div>
    <?php endif; ?>
    <?php if (!empty($price_revision)): ?>
    <div class="alert alert-danger d-flex align-items-center py-2 mb-3" role="alert" id="priceTicker">
        <i class="fas fa-fire me-2"></i>
        <div class="small"><strong>📢 <?= htmlspecialchars($price_revision['announcement_title'] ?? 'Price revision') ?>:</strong>
            rates ₹<?= number_format((float)($price_revision['new_price'] ?? 0)) ?>/sqft from <?= htmlspecialchars($price_revision['effective_date'] ?? '') ?>
            (<strong><span id="tickerDays">…</span> days left</strong>) — lock the old rate with a free 24h hold now!
        </div>
    </div>
    <script>
    (function () {
        try {
            var ms = new Date('<?= htmlspecialchars($price_revision['effective_date'] ?? '') ?>T00:00:00') - Date.now();
            document.getElementById('tickerDays').textContent = Math.max(0, Math.ceil(ms / 86400000));
        } catch (e) {}
    })();
    </script>
    <?php endif; ?>
    <div class="map-stats-bar" id="mapStats">
        <span class="map-stat" data-status="available"><span class="dot"></span> Available: <strong id="statAvail">0</strong></span>
        <span class="map-stat" data-status="booked"><span class="dot"></span> Booked: <strong id="statBooked">0</strong></span>
        <span class="map-stat" data-status="sold"><span class="dot"></span> Sold: <strong id="statSold">0</strong></span>
        <span class="map-stat" data-status="hold"><span class="dot"></span> Hold: <strong id="statHold">0</strong></span>
        <span class="map-stat ms-auto text-muted">Total Value: <strong id="statValue">₹0</strong></span>
    </div>
    <div class="map-filter-bar mb-3">
        <button class="btn btn-sm active" data-filter="all">All</button>
        <button class="btn btn-sm" data-filter="available" >Available</button>
        <button class="btn btn-sm" data-filter="booked" >Booked</button>
        <button class="btn btn-sm" data-filter="sold" >Sold</button>
        <button class="btn btn-sm" data-filter="hold" >Hold</button>
    </div>
    <div id="plotMap"></div>
    <div class="text-center mt-3">
        <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($colony['slug'] ?? '') ?>/plots" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-table me-1"></i>View Plot Table
        </a>
        <a href="tel:+919277121112" class="btn btn-outline-success btn-sm ms-2">
            <i class="fas fa-phone me-1"></i>Call for Inquiry
        </a>
    </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    var colonyId = <?= (int)($colony['id'] ?? 0) ?>;
    var baseUrl = '<?= BASE_URL ?>';
    var map = L.map('plotMap', { zoomControl: true, attributionControl: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    var geojsonLayer = null;
    // Vastu badge (same scale as admin map; unknown facing => no row).
    function vastuRowPub(facing) {
        var f = String(facing || '').toLowerCase().replace(/[\s_-]+/g, '');
        var table = [
            ['northeast,ne,ishan', 98, 'Highly Auspicious'], ['northwest,nw', 85, 'Auspicious'],
            ['southeast,se', 80, 'Auspicious'], ['southwest,sw', 62, 'Remedies Advised'],
            ['east,purab', 92, 'Very Auspicious'], ['north,uttar', 90, 'Very Auspicious'],
            ['west,paschim', 78, 'Moderate'], ['south,dakshin', 68, 'Remedies Advised']
        ];
        for (var i = 0; i < table.length; i++) {
            var keys = table[i][0].split(',');
            for (var j = 0; j < keys.length; j++) {
                if (f.indexOf(keys[j]) !== -1) {
                    return '<div class="info-row"><span class="label">Vastu</span><span class="value">🧭 ' +
                        table[i][1] + '% ' + table[i][2] + '</span></div>';
                }
            }
        }
        return '';
    }
    // Deep link: ?plot=A-12 focuses + pulses + opens that plot (ref stays bound).
    function focusPlotParam() {
        try {
            var m = new URLSearchParams(window.location.search).get('plot');
            return (m || '').trim().toLowerCase();
        } catch (e) { return ''; }
    }
    function loadGeoJson() {
        fetch(baseUrl + '/api/colony/' + colonyId + '/map/geojson')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (geojsonLayer) { map.removeLayer(geojsonLayer); }
                var statusLabels = { available: 'Available', booked: 'Booked', sold: 'Sold', hold: 'On Hold', reserved: 'Reserved' };
                geojsonLayer = L.geoJSON(data, {
                    style: function(f) {
                        var colors = { available: '#22c55e', booked: '#eab308', sold: '#ef4444', hold: '#6b7280', reserved: '#f97316' };
                        return { fillColor: colors[f.properties.status] || '#94a3b8', color: '#1e293b', weight: 1, fillOpacity: 0.7 };
                    },
                    onEachFeature: function(f, layer) {
                        var p = f.properties;
                        var html = '<div class="plot-popup"><h6>Plot #' + p.plot_number +
                            ' <span class="badge bg-' + (p.status === 'available' ? 'success' : p.status === 'booked' ? 'warning text-dark' : p.status === 'sold' ? 'danger' : 'secondary') +
                            '">' + (statusLabels[p.status] || p.status) + '</span></h6>' +
                            '<div class="info-row"><span class="label">Block</span><span class="value">' + (p.block || '-') + '</span></div>' +
                            '<div class="info-row"><span class="label">Area</span><span class="value">' + (p.area_sqft || 0) + ' sqft</span></div>' +
                            '<div class="info-row"><span class="label">Size</span><span class="value">' + (p.width_ft || '-') + 'x' + (p.length_ft || '-') + '</span></div>' +
                            (p.price_per_sqft ? '<div class="info-row"><span class="label">Rate</span><span class="value">₹' + Number(p.price_per_sqft).toLocaleString() + '/sqft</span></div>' : '') +
                            (p.total_price ? '<div class="info-row"><span class="label">Price</span><span class="value fw-bold">₹' + Number(p.total_price).toLocaleString() + '</span></div>' : '') +
                            (p.corner_plot ? '<div class="info-row"><span class="label">Corner Plot</span><span class="value">✓</span></div>' : '') +
                            (p.park_facing ? '<div class="info-row"><span class="label">Park Facing</span><span class="value">✓</span></div>' : '') +
                            vastuRowPub(p.facing) +
                            '<hr class="my-2"><a href="' + baseUrl + '/colony/' + (colonySlug || '') + '/plots?block=' + encodeURIComponent(p.block || '') + '" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-eye me-1"></i>View Details</a>' +
                            (p.status === 'available' ? '<button onclick="openHoldModal(' + p.plot_id + ', \'' + String(p.plot_number).replace(/'/g, "\\'") + '\')" class="btn btn-sm btn-warning w-100 mt-2"><i class="fas fa-pause-circle me-1"></i>Request 24h Hold</button>' : '') + '</div>';
                        layer.bindPopup(html, { maxWidth: 300 });
                        layer.on('mouseover', function() { this.setStyle({ fillOpacity: 0.95, weight: 2 }); });
                        layer.on('mouseout', function() { if (geojsonLayer) geojsonLayer.resetStyle(this); });
                    }
                }).addTo(map);
                if (data.features && data.features.length) {
                    map.fitBounds(geojsonLayer.getBounds(), { padding: [30, 30], maxZoom: 18 });
                } else { map.setView([26.76, 83.37], 13); }
                updateStats(data.features || []);
                (function () {
                    var want = focusPlotParam();
                    if (!want || !geojsonLayer) return;
                    geojsonLayer.eachLayer(function (layer) {
                        var pn = String((layer.feature.properties || {}).plot_number || '').toLowerCase();
                        if (pn === want) {
                            if (layer._path) layer._path.classList.add('plot-target-pulse');
                            try { map.setView(layer.getBounds().getCenter(), 18, { animate: true }); } catch (e) {}
                            try { layer.openPopup(); } catch (e) {}
                        }
                    });
                })();
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
        document.getElementById('statValue').textContent = '₹' + totalValue.toLocaleString();
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
    window.colonySlug = '<?= addslashes($colony['slug'] ?? '') ?>';
    loadGeoJson();
})();
</script>

<!-- 24h Hold Request Modal -->
<div class="modal fade" id="holdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header">
            <h6 class="modal-title"><i class="fas fa-pause-circle me-1"></i>Request 24-Hour Hold — Plot <span id="holdPlotNo"></span></h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p class="small text-muted">Fill your details — our team confirms the hold on call/SMS. <?= $refCode !== '' ? 'Associate ref <strong>' . htmlspecialchars($refCode) . '</strong> auto-attached.' : '' ?></p>
            <div class="mb-2"><label class="form-label small">Your Name</label>
                <input type="text" id="holdName" class="form-control form-control-sm" placeholder="Full name"></div>
            <div class="mb-2"><label class="form-label small">Mobile Number</label>
                <input type="tel" id="holdPhone" class="form-control form-control-sm" placeholder="10-digit mobile" maxlength="13"></div>
            <div id="holdMsg" class="small mt-2"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-warning btn-sm" id="holdSubmit"><i class="fas fa-check me-1"></i>Confirm Hold Request</button>
        </div>
    </div></div>
</div>
<script>
(function () {
    var holdPlotId = 0;
    var csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
    var refCode = <?= json_encode($refCode) ?>;
    var baseUrl = <?= json_encode(BASE_URL) ?>;
    window.openHoldModal = function (plotId, plotNo) {
        holdPlotId = plotId;
        document.getElementById('holdPlotNo').textContent = plotNo;
        document.getElementById('holdMsg').innerHTML = '';
        var m = new bootstrap.Modal(document.getElementById('holdModal'));
        m.show();
    };
    document.getElementById('holdSubmit').addEventListener('click', function () {
        var btn = this;
        var name = document.getElementById('holdName').value.trim();
        var phone = document.getElementById('holdPhone').value.trim();
        var msg = document.getElementById('holdMsg');
        if (!name || phone.replace(/\D/g, '').length < 10) {
            msg.innerHTML = '<span class="text-danger">Please enter your name and a valid 10-digit mobile number.</span>';
            return;
        }
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
        fetch(baseUrl + '/colony/hold-request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken },
            body: 'plot_id=' + encodeURIComponent(holdPlotId) + '&name=' + encodeURIComponent(name) +
                  '&phone=' + encodeURIComponent(phone) + '&ref_code=' + encodeURIComponent(refCode) +
                  '&csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(function (r) { return r.json().then(function (d) { return { code: r.status, body: d }; }); })
        .then(function (res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Confirm Hold Request';
            if (res.body && res.body.success) {
                msg.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + res.body.message + '</span>';
            } else {
                msg.innerHTML = '<span class="text-danger">' + ((res.body && res.body.error) || 'Failed. Please try again.') + '</span>';
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Confirm Hold Request';
            msg.innerHTML = '<span class="text-danger">Network error. Please try again.</span>';
        });
    });
})();
</script>
