<?php $plot = $plot ?? []; $priceHistory = $priceHistory ?? []; $nearbyPlots = $nearbyPlots ?? []; ?>
<style>
.plot-gallery-img { width: 100%; height: 350px; object-fit: cover; border-radius: 12px; }
.detail-card { border: 1px solid #e8e8e8; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.detail-label { color: #666; font-size: 0.85rem; }
.detail-value { font-size: 1.1rem; font-weight: 600; color: #0d9488; }
.price-tag { font-size: 2rem; font-weight: 800; color: #2e7d32; }
.spec-item { padding: 16px; text-align: center; border: 1px solid #e8e8e8; border-radius: 10px; }
.spec-item i { font-size: 1.5rem; color: #0d9488; margin-bottom: 8px; }
</style>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('colony_breadcrumb_home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/plots"><?= __('colony_breadcrumb_plots') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($plot['colony_slug'] ?? '') ?>/plots"><?= htmlspecialchars($plot['colony_name'] ?? '') ?></a></li>
            <li class="breadcrumb-item active"><?= sprintf(__('plot_detail_plot'), htmlspecialchars($plot['plot_number'] ?? '')) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Left: Plot Image / Map -->
        <div class="col-lg-7 mb-4">
            <?php if (!empty($plot['image_path'])): ?>
                <img src="<?= htmlspecialchars($plot['image_path'] ?? '') ?>" alt="Plot <?= htmlspecialchars($plot['plot_number'] ?? '') ?>" class="plot-gallery-img">
            <?php else: ?>
                <div class="plot-gallery-img bg-light d-flex align-items-center justify-content-center style-68724">
                    <div class="text-center text-muted">
                        <i class="fas fa-map-marked-alt fa-4x mb-3"></i>
                        <p class="mb-0"><?= __('plot_detail_location_image') ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($plot['map_link'])): ?>
                <div class="mt-3">
                    <a href="<?= htmlspecialchars($plot['map_link'] ?? '') ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-map"></i> <?= __('plot_detail_view_map') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Plot Info -->
        <div class="col-lg-5 mb-4">
            <div class="detail-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="fw-bold mb-1"><?= sprintf(__('plot_detail_heading'), htmlspecialchars($plot['plot_number'] ?? '')) ?></h2>
                        <p class="text-muted mb-0">
                            <i class="fas fa-building"></i> <?= htmlspecialchars($plot['colony_name'] ?? '') ?><br>
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars(($plot['state_name'] ?? '') . ($plot['district_name'] ? ', ' . $plot['district_name'] : '')) ?>
                        </p>
                    </div>
                    <?php 
                        $statusColor = match($plot['status'] ?? 'available') { 'available'=>'success', 'booked'=>'warning', 'sold'=>'danger', 'hold'=>'secondary', default=>'secondary' };
                        $statusLabel = match($plot['status'] ?? 'available') { 'available'=>__('compare_status_available'), 'booked'=>__('compare_status_booked'), 'sold'=>__('compare_status_sold'), 'hold'=>__('compare_status_hold'), default=>ucfirst($plot['status'] ?? '') };
                    ?>
                    <span class="badge bg-<?= $statusColor ?> fs-6 px-3 py-2"><?= $statusLabel ?></span>
                </div>

                <div class="price-tag mb-3">₹<?= number_format(intval($plot['total_price'] ?? 0)) ?></div>
                
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="spec-item">
                            <i class="fas fa-vector-square"></i>
                            <div class="detail-value"><?= number_format(floatval($plot['area_sqft'] ?? 0)) ?></div>
                            <div class="detail-label"><?= __('plot_detail_sqft') ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="spec-item">
                            <i class="fas fa-arrows-alt"></i>
                            <div class="detail-value"><?= htmlspecialchars($plot['dimension_label'] ?? '—') ?></div>
                            <div class="detail-label"><?= __('plot_detail_dimension') ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="spec-item">
                            <i class="fas fa-tag"></i>
                            <div class="detail-value">₹<?= number_format(floatval($plot['price_per_sqft'] ?? 0)) ?></div>
                            <div class="detail-label"><?= __('plot_detail_per_sqft') ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="spec-item">
                            <i class="fas fa-layer-group"></i>
                            <div class="detail-value"><?= htmlspecialchars($plot['block'] ?? '—') ?></div>
                            <div class="detail-label"><?= __('colony_block_label') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="mb-3">
                    <h6 class="fw-bold"><?= __('plot_detail_features') ?></h6>
                    <div>
                        <?php if ($plot['corner_plot'] ?? false): ?>
                            <span class="badge bg-primary me-1"><?= __('compare_corner_plot') ?></span>
                        <?php endif; ?>
                        <?php if ($plot['park_facing'] ?? false): ?>
                            <span class="badge bg-success me-1"><?= __('colony_park_facing') ?></span>
                        <?php endif; ?>
                        <?php if (!empty($plot['facing'])): ?>
                            <span class="badge bg-info me-1"><?= __('plot_detail_facing') ?> <?= htmlspecialchars($plot['facing'] ?? '') ?></span>
                        <?php endif; ?>
                        <?php if ($plot['road_width_ft'] ?? false): ?>
                            <span class="badge bg-secondary"><?= __('compare_road_width') ?> <?= floatval($plot['road_width_ft']) ?>ft</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Description -->
                <?php if (!empty($plot['description'])): ?>
                    <div class="mb-3">
                        <h6 class="fw-bold"><?= __('plot_detail_description') ?></h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($plot['description'] ?? '')) ?></p>
                    </div>
                <?php endif; ?>

                <!-- CTA Buttons -->
                <div class="d-grid gap-2">
                    <?php if (($plot['status'] ?? '') === 'available'): ?>
                        <a href="<?= BASE_URL ?>/plot/<?= $plot['id'] ?>/book" class="btn btn-success btn-lg">
                            <i class="fas fa-file-contract"></i> <?= __('plot_detail_book_plot') ?>
                        </a>
                        <a href="<?= BASE_URL ?>/contact?plot=<?= $plot['id'] ?>&subject=I%27m%20interested%20in%20Plot%20<?= urlencode($plot['plot_number'] ?? '') ?>" class="btn btn-primary">
                            <i class="fas fa-phone"></i> <?= __('colony_enquire_now') ?>
                        </a>
                        <a href="<?= BASE_URL ?>/schedule-visit?plot=<?= $plot['id'] ?>" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-check"></i> <?= __('plot_detail_schedule_visit') ?>
                        </a>
                        <button type="button" class="btn btn-outline-info" id="addCompareBtn" onclick="addToCompare(<?= $plot['id'] ?? 0 ?>)">
                            <i class="fas fa-balance-scale"></i> <?= __('plot_detail_add_compare') ?>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="fas fa-lock"></i> <?= __('colony_not_available') ?>
                        </button>
                        <a href="<?= BASE_URL ?>/contact?subject=Similar%20plots%20to%20<?= urlencode($plot['plot_number'] ?? '') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i> <?= __('plot_detail_find_similar') ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="text-center mt-2" id="compareMsg" class="style-2248">
                    <small class="text-success"><i class="fas fa-check-circle"></i> <?= __('plot_detail_added_compare') ?> <a href="<?= BASE_URL ?>/compare"><?= __('compare_plot_header') ?></a></small>
                </div>
            </div>

            <!-- Negotiated Price Info -->
            <?php if (!empty($plot['negotiated_price']) && $plot['negotiated_price'] != $plot['total_price']): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong><?= __('plot_detail_negotiated') ?></strong> <?= __('plot_detail_negotiated_desc') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Colony Amenities -->
    <?php if (!empty($plot['amenities'])): 
        $amenities = is_string($plot['amenities']) ? json_decode($plot['amenities'], true) : (is_array($plot['amenities']) ? $plot['amenities'] : []); 
    ?>
        <?php if (!empty($amenities)): ?>
        <div class="detail-card mt-3">
            <h4 class="fw-bold mb-3"><?= __('plot_detail_colony_amenities') ?></h4>
            <div class="row g-2">
                <?php foreach ($amenities as $amenity): ?>
                    <?php $a = is_string($amenity) ? str_replace(['[', ']', '"', '\\'], '', $amenity) : ''; ?>
                    <?php if (!empty($a)): ?>
                    <div class="col-md-4 col-6">
                        <span class="amenity-tag style-80567">
                            <i class="fas fa-check-circle text-success me-1"></i> <?= htmlspecialchars(trim($a)) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Price History -->
    <?php if (!empty($priceHistory)): ?>
    <div class="detail-card mt-3">
        <h4 class="fw-bold mb-3"><i class="fas fa-history"></i> <?= __('plot_detail_price_history') ?></h4>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th><?= __('plot_detail_date') ?></th><th><?= __('plot_detail_old_price') ?></th><th><?= __('plot_detail_new_price') ?></th><th><?= __('plot_detail_change_type') ?></th><th><?= __('plot_detail_reason') ?></th></tr></thead>
                <tbody>
                    <?php foreach ($priceHistory as $ph): ?>
                    <tr>
                        <td><?= htmlspecialchars($ph['created_at'] ?? '') ?></td>
                        <td>₹<?= number_format(intval($ph['old_price'] ?? 0)) ?></td>
                        <td>₹<?= number_format(intval($ph['new_price'] ?? 0)) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($ph['change_type'] ?? '') ?></span></td>
                        <td><?= htmlspecialchars($ph['reason'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Nearby Plots -->
    <?php if (!empty($nearbyPlots)): ?>
    <div class="mt-4">
        <h4 class="fw-bold mb-3"><i class="fas fa-th"></i> Nearby Plots in Block <?= htmlspecialchars($plot['block'] ?? '') ?></h4>
        <div class="row g-3">
            <?php foreach ($nearbyPlots as $np): ?>
                <?php 
                    $npStatusColor = match($np['status'] ?? 'available') { 'available'=>'success', 'booked'=>'warning', 'sold'=>'danger', default=>'secondary' };
                    $npStatusLabel = match($np['status'] ?? 'available') { 'available'=>__('compare_status_available'), 'booked'=>__('compare_status_booked'), 'sold'=>__('compare_status_sold'), default=>ucfirst($np['status'] ?? '') };
                ?>
                <div class="col-md-4">
                    <div class="detail-card p-3">
                        <div class="d-flex justify-content-between">
                            <strong>Plot <?= htmlspecialchars($np['plot_number'] ?? '') ?></strong>
                            <span class="badge bg-<?= $npStatusColor ?>"><?= $npStatusLabel ?></span>
                        </div>
                        <div class="text-muted small">
                            <?= number_format(floatval($np['area_sqft'] ?? 0)) ?> sqft | <?= htmlspecialchars($np['dimension_label'] ?? '') ?> | ₹<?= number_format(intval($np['total_price'] ?? 0)) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function addToCompare(plotId) {
    fetch('<?= BASE_URL ?>/compare/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= $csrf_token ?? '' ?>'
        },
        body: 'plot_id=' + plotId
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            document.getElementById('compareMsg').style.display = 'block';
            document.getElementById('addCompareBtn').classList.add('active');
            document.getElementById('addCompareBtn').innerHTML = '<i class="fas fa-check"></i> Added to Compare';
        } else {
            alert(d.message);
        }
    })
    .catch(() => alert('Could not add to compare. Please try again.'));
}
</script>
