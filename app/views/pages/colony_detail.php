<?php
$colony = $colony ?? [];
$availablePlots = $availablePlots ?? [];
$highlights = !empty($colony['key_highlights']) ? (json_decode($colony['key_highlights'], true) ?? []) : [];
$nearbyPlaces = !empty($colony['nearby_places']) ? (json_decode($colony['nearby_places'], true) ?? []) : [];
$galleryImages = !empty($colony['gallery_images']) ? (json_decode($colony['gallery_images'], true) ?? []) : [];
$amenities = array_filter(array_map('trim', explode("\n", $colony['amenities'] ?? '')));
$page_title = $colony['meta_title'] ?: ($colony['name'] . ' - APS Dream Home');
$page_description = $colony['meta_description'] ?: ($colony['name'] . ' - Premium residential plots and properties');
$bannerImage = $colony['banner_image'] ? BASE_URL . '/' . ltrim($colony['banner_image'], '/') : '';
?>
<style>
.hero-section { position:relative; min-height:60vh; display:flex; align-items:center; background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%); overflow:hidden; }
.hero-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:url('<?php echo $bannerImage ?: BASE_URL . '/assets/images/default-banner.jpg'; ?>') center/cover no-repeat; opacity:0.3; }
.hero-content { position:relative; z-index:2; color:#fff; padding:80px 0; }
.hero-content h1 { font-size:3rem; font-weight:800; margin-bottom:1rem; text-shadow:0 2px 10px rgba(0,0,0,0.3); }
.hero-content .badge-feat { background:rgba(255,193,7,0.9); color:#000; padding:8px 20px; border-radius:50px; font-weight:600; }
.stat-card { background:rgba(255,255,255,0.1); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.2); border-radius:16px; padding:24px; text-align:center; }
.stat-card .num { font-size:2rem; font-weight:700; }
.stat-card .lbl { font-size:.85rem; opacity:.8; }
.highlight-card { background:#fff; border-radius:16px; padding:24px; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,0.08); transition:transform .3s; height:100%; }
.highlight-card:hover { transform:translateY(-5px); }
.highlight-card .icon { width:60px; height:60px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; font-size:1.5rem; }
.amenity-tag { display:inline-block; padding:8px 20px; background:#f0f4ff; color:#2563eb; border-radius:50px; font-size:.9rem; margin:4px; font-weight:500; }
.plot-card { border:1px solid #e2e8f0; border-radius:12px; padding:16px; transition:all .2s; }
.plot-card:hover { box-shadow:0 4px 15px rgba(0,0,0,0.1); }
.plot-card .price { font-size:1.2rem; font-weight:700; color:#2563eb; }
.contact-card { background:linear-gradient(135deg,#0d9488,#0f766e); border-radius:16px; padding:32px; color:#fff; }
.contact-card a { color:#fff; text-decoration:none; }
.gallery-img { border-radius:12px; width:100%; height:200px; object-fit:cover; }
</style>

<!-- Hero -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <?php if ($colony['is_featured'] ?? 0): ?><span class="badge-feat mb-3 d-inline-block"><i class="fas fa-star me-1"></i><?= __('colony_featured_project') ?></span><?php endif; ?>
                <h1><?php echo htmlspecialchars($colony['name'] ?? 'Our Project'); ?></h1>
                <p class="lead mb-4 opacity-75"><?php echo htmlspecialchars(substr($colony['description'] ?? '', 0, 200)); ?></p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#contact" class="btn btn-light btn-lg"><i class="fas fa-phone me-2"></i><?= __('colony_enquire_now') ?></a>
                    <?php if ($colony['brochure_path'] ?? ''): ?>
                    <a href="<?php echo BASE_URL . '/' . ltrim($colony['brochure_path'], '/'); ?>" class="btn btn-outline-light btn-lg" target="_blank"><i class="fas fa-download me-2"></i><?= __('colony_download_brochure') ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="row g-3">
                    <div class="col-6"><div class="stat-card"><div class="num"><?php echo $colony['total_plots'] ?? 0; ?></div><div class="lbl"><?= __('colony_total_plots') ?></div></div></div>
                    <div class="col-6"><div class="stat-card"><div class="num"><?php echo $colony['available_plots'] ?? 0; ?></div><div class="lbl"><?= __('colony_available') ?></div></div></div>
                    <div class="col-6"><div class="stat-card"><div class="num">₹<?php echo number_format($colony['starting_price'] ?? 0); ?></div><div class="lbl"><?= __('colony_starting_price') ?></div></div></div>
                    <div class="col-6"><div class="stat-card"><div class="num"><?php echo count($amenities); ?>+</div><div class="lbl"><?= __('colony_amenities') ?></div></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Key Highlights -->
<?php if (!empty($highlights)): ?>
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5"><?= sprintf(__('colony_why_choose'), '<span class="text-primary">' . htmlspecialchars($colony['name'] ?? '') . '</span>') ?></h2>
        <div class="row g-4">
            <?php foreach ($highlights as $h): ?>
            <div class="col-md-4 col-sm-6">
                <div class="highlight-card">
                    <?php if (is_array($h)): ?>
                        <div class="icon bg-primary bg-opacity-10 text-primary"><i class="<?php echo $h['icon'] ?? 'fas fa-check-circle'; ?>"></i></div>
                        <h5><?php echo htmlspecialchars($h['title'] ?? ''); ?></h5>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($h['desc'] ?? ''); ?></p>
                    <?php else: ?>
                        <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-check-circle"></i></div>
                        <h5><?php echo htmlspecialchars($h); ?></h5>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- About / Description -->
<?php if ($colony['description'] ?? ''): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-5 align-items-center">
            <?php if ($colony['image_path'] ?? ''): ?>
            <div class="col-lg-6">
                <img src="<?= BASE_URL ?>/<?php echo htmlspecialchars($colony['image_path'] ?? ''); ?>" alt="<?php echo htmlspecialchars($colony['name'] ?? ''); ?>" class="img-fluid rounded-4 shadow" loading="lazy">
            </div>
            <?php endif; ?>
            <div class="col-lg-<?php echo ($colony['image_path'] ?? '') ? '6' : '12'; ?>">
                <h2 class="mb-4"><?= sprintf(__('colony_about'), '<span class="text-primary">' . htmlspecialchars($colony['name'] ?? '') . '</span>') ?></h2>
                <?php echo nl2br(htmlspecialchars($colony['description'] ?? '')); ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Amenities -->
<?php if (!empty($amenities)): ?>
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5"><i class="fas fa-concierge-bell text-primary me-2"></i><?= __('colony_amenities_heading') ?></h2>
        <div class="text-center">
            <?php foreach ($amenities as $a): ?><span class="amenity-tag"><i class="fas fa-check-circle me-1"></i><?php echo htmlspecialchars($a); ?></span><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Available Plots (if public) -->
<?php if (($colony['show_plots_publicly'] ?? 0) && !empty($availablePlots)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5"><i class="fas fa-map-marked-alt text-primary me-2"></i><?= __('colony_available_plots_heading') ?></h2>
        <div class="row g-4">
            <?php foreach (array_slice($availablePlots, 0, 12) as $p): ?>
            <div class="col-md-4 col-sm-6">
                <div class="plot-card bg-white">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0"><?= sprintf(__('colony_plot_number'), htmlspecialchars($p['plot_number'] ?? 'N/A')) ?></h6>
                        <span class="badge bg-success"><?= __('colony_available') ?></span>
                    </div>
                    <p class="text-muted small mb-2"><?php echo htmlspecialchars($p['block'] ?? ''); ?> &bull; <?php echo $p['area_sqft'] ?? 0; ?> sqft</p>
                    <div class="price">₹<?php echo number_format($p['total_price'] ?? 0); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($availablePlots) > 12): ?>
        <div class="text-center mt-4"><a href="<?php echo BASE_URL; ?>/colony/<?php echo htmlspecialchars($colony['slug'] ?? ''); ?>/plots" class="btn btn-outline-primary"><?= sprintf(__('colony_view_all_plots'), count($availablePlots)) ?></a></div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Interactive Plot Map -->
<?php if (!empty($mapData['features'])): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-4"><i class="fas fa-map-marked-alt text-primary me-2"></i><?= __('colony_plot_map_heading') ?? 'Plot Map' ?></h2>
        <div class="d-flex flex-wrap gap-2 mb-3 justify-content-center" id="plotFilterBar">
            <button class="btn btn-sm btn-outline-secondary active" data-filter="all">All</button>
            <button class="btn btn-sm btn-outline-success" data-filter="available">Available</button>
            <button class="btn btn-sm btn-outline-warning" data-filter="booked">Booked</button>
            <button class="btn btn-sm btn-outline-danger" data-filter="sold">Sold</button>
            <button class="btn btn-sm btn-outline-secondary" data-filter="hold">On Hold</button>
        </div>
        <div style="position:relative; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.1);">
            <div id="customerPlotMap" style="height:500px; width:100%;"></div>
        </div>
        <div class="d-flex flex-wrap gap-3 justify-content-center mt-3 small text-muted">
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:#22c55e;margin-right:4px;vertical-align:middle"></span> Available</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:#eab308;margin-right:4px;vertical-align:middle"></span> Booked</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:#ef4444;margin-right:4px;vertical-align:middle"></span> Sold</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:#6b7280;margin-right:4px;vertical-align:middle"></span> On Hold</span>
        </div>
    </div>
</section>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    var mapData = <?php echo json_encode($mapData ?? []); ?>;
    var container = document.getElementById('customerPlotMap');
    if (!container || !mapData.features || !mapData.features.length) return;
    var map = L.map('customerPlotMap', { zoomControl: true, attributionControl: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    var geojsonLayer = L.geoJSON(mapData, {
        style: function(f) {
            var colors = { available: '#22c55e', booked: '#eab308', sold: '#ef4444', hold: '#6b7280', reserved: '#f97316' };
            return { fillColor: colors[f.properties.status] || '#94a3b8', color: '#1e293b', weight: 1, fillOpacity: 0.7 };
        },
        onEachFeature: function(f, layer) {
            var p = f.properties;
            var statusBadge = { available: 'success', booked: 'warning text-dark', sold: 'danger', hold: 'secondary', reserved: 'warning' };
            var html = '<div style="min-width:200px;font-family:sans-serif">' +
                '<h6 style="margin-bottom:6px;font-weight:600">Plot #' + p.plot_number +
                ' <span class="badge bg-' + (statusBadge[p.status] || 'secondary') + '">' + p.status + '</span></h6>' +
                '<div style="display:flex;justify-content:space-between;padding:2px 0;font-size:13px"><span style="color:#64748b">Block</span><span style="font-weight:500">' + (p.block || '-') + '</span></div>' +
                '<div style="display:flex;justify-content:space-between;padding:2px 0;font-size:13px"><span style="color:#64748b">Area</span><span style="font-weight:500">' + (p.area_sqft || 0) + ' sqft</span></div>' +
                '<div style="display:flex;justify-content:space-between;padding:2px 0;font-size:13px"><span style="color:#64748b">Size</span><span style="font-weight:500">' + (p.width_ft || '-') + 'x' + (p.length_ft || '-') + '</span></div>' +
                (p.corner_plot ? '<div style="display:flex;justify-content:space-between;padding:2px 0;font-size:13px"><span style="color:#64748b">Corner Plot</span><span style="color:#16a34a;font-weight:500">&#10003;</span></div>' : '') +
                (p.park_facing ? '<div style="display:flex;justify-content:space-between;padding:2px 0;font-size:13px"><span style="color:#64748b">Park Facing</span><span style="color:#16a34a;font-weight:500">&#10003;</span></div>' : '') +
                '<hr style="margin:6px 0"><div style="display:flex;justify-content:space-between;padding:2px 0;font-size:14px"><span style="color:#64748b">Price</span><span style="font-weight:700;color:#0d9488">&#8377;' + Number(p.total_price || 0).toLocaleString() + '</span></div></div>';
            layer.bindPopup(html, { maxWidth: 300 });
            layer.on('mouseover', function() { this.setStyle({ fillOpacity: 0.95, weight: 2 }); });
            layer.on('mouseout', function() { geojsonLayer.resetStyle(this); });
        }
    }).addTo(map);
    if (mapData.features.length) {
        map.fitBounds(geojsonLayer.getBounds(), { padding: [30, 30], maxZoom: 18 });
    } else {
        map.setView([26.76, 83.37], 13);
    }
    document.querySelectorAll('#plotFilterBar .btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#plotFilterBar .btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var filter = this.getAttribute('data-filter');
            geojsonLayer.eachLayer(function(layer) {
                var s = layer.feature.properties.status;
                var match = filter === 'all' || s === filter || (filter === 'hold' && (s === 'hold' || s === 'reserved'));
                if (layer._path) layer._path.style.display = match ? '' : 'none';
            });
        });
    });
})();
</script>
<?php endif; ?>

<!-- Gallery -->
<?php if (!empty($galleryImages)): ?>
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5"><i class="fas fa-images text-primary me-2"></i><?= __('colony_gallery_heading') ?></h2>
        <div class="row g-3">
            <?php foreach ($galleryImages as $img): ?>
            <div class="col-md-4 col-sm-6">
                <a href="<?php echo BASE_URL . '/' . ltrim($img, '/'); ?>" data-lightbox="gallery">
                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars(ltrim($img, '/')); ?>" alt="Gallery" class="gallery-img" loading="lazy">
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Video -->
<?php if ($colony['youtube_video_url'] ?? ''): ?>
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5"><i class="fas fa-video text-primary me-2"></i><?= __('colony_video_tour_heading') ?></h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow">
                    <iframe src="<?php echo htmlspecialchars($colony['youtube_video_url']); ?>" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Nearby Places -->
<?php if (!empty($nearbyPlaces)): ?>
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center mb-5"><i class="fas fa-location-dot text-primary me-2"></i><?= __('colony_nearby_places_heading') ?></h2>
        <div class="row g-4">
            <?php foreach ($nearbyPlaces as $np): ?>
            <div class="col-md-4 col-sm-6">
                <div class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-light">
                    <div class="icon-circle bg-primary bg-opacity-10 text-primary" style="width:45px;height:45px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars(is_array($np) ? ($np['name'] ?? '') : $np); ?></strong>
                        <br><small class="text-muted"><?php echo htmlspecialchars(is_array($np) ? ($np['distance'] ?? '') : ''); ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Map -->
<?php if ($colony['map_link'] ?? ''): ?>
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5"><i class="fas fa-map text-primary me-2"></i><?= __('colony_location_map_heading') ?></h2>
        <div class="rounded-4 overflow-hidden shadow">
            <iframe src="<?php echo htmlspecialchars($colony['map_link']); ?>" width="100%" height="400" style="border:0" allowfullscreen loading="lazy"></iframe>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Contact CTA -->
<section class="py-5" id="contact">
    <div class="container">
        <div class="contact-card text-center">
            <h3 class="mb-3"><?= sprintf(__('colony_interested_in'), htmlspecialchars($colony['name'] ?? '')) ?></h3>
            <p class="mb-4 opacity-75"><?= __('colony_get_in_touch') ?></p>
            <div class="d-flex flex-wrap justify-content-center gap-4 mb-4">
                <?php if ($colony['contact_phone'] ?? ''): ?>
                <a href="tel:<?php echo htmlspecialchars($colony['contact_phone']); ?>" class="btn btn-light btn-lg"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($colony['contact_phone']); ?></a>
                <?php endif; ?>
                <?php if ($colony['contact_email'] ?? ''): ?>
                <a href="mailto:<?php echo htmlspecialchars($colony['contact_email']); ?>" class="btn btn-outline-light btn-lg"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($colony['contact_email']); ?></a>
                <?php endif; ?>
            </div>
            <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-warning btn-lg"><i class="fas fa-paper-plane me-2"></i><?= __('colony_send_enquiry') ?></a>
        </div>
    </div>
</section>
