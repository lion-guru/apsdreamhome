<?php $colony = $colony ?? []; $plots = $plots ?? []; $dimensions = $dimensions ?? []; $blocks = $blocks ?? []; $stats = $stats ?? []; ?>
<style>
.plot-card { transition: all 0.3s ease; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; }
.plot-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
.plot-card .plot-number { font-size: 1.1rem; font-weight: 700; color: #0d9488; }
.plot-card .plot-price { font-size: 1.3rem; font-weight: 700; color: #2e7d32; }
.plot-card .plot-detail { font-size: 0.9rem; color: #555; }
.plot-card .status-badge { position: absolute; top: 12px; right: 12px; }
.plot-card .amenity-tag { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 0.78rem; background: #f0fdfa; color: #0d9488; margin: 2px; }
.dimension-btn { padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; border: 1px solid #c5cae9; cursor: pointer; transition: all 0.2s; background: white; color: #333; }
.dimension-btn:hover, .dimension-btn.active { background: #0d9488; color: white; border-color: #0d9488; }
.filter-section { background: #f0fdfa; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
.stat-card { padding: 16px; border-radius: 10px; text-align: center; }
.stat-card h3 { font-size: 1.8rem; font-weight: 800; margin: 0; }
.stat-card p { font-size: 0.85rem; margin: 4px 0 0; opacity: 0.85; }
</style>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('colony_breadcrumb_home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/plots"><?= __('colony_breadcrumb_plots') ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($colony['name'] ?? '') ?></li>
        </ol>
    </nav>

    <!-- Colony Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-2 fw-bold"><?= htmlspecialchars($colony['name'] ?? '') ?></h1>
            <p class="text-muted mb-2">
                <i class="fas fa-map-marker-alt text-danger"></i> 
                <?= htmlspecialchars(($colony['state_name'] ?? '') . ($colony['district_name'] ? ', ' . $colony['district_name'] : '')) ?>
            </p>
            <p><?= htmlspecialchars($colony['description'] ?? '') ?></p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= $colony['map_link'] ?? '#' ?>" target="_blank" class="btn btn-outline-primary me-2">
                <i class="fas fa-map"></i> <?= __('colony_view_on_map') ?>
            </a>
            <a href="<?= BASE_URL ?>/contact" class="btn btn-primary">
                <i class="fas fa-phone"></i> <?= __('colony_enquire_now') ?>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4 g-3">
        <div class="col-4 col-md-2">
            <div class="stat-card bg-primary text-white">
                <h3><?= intval($stats['total'] ?? 0) ?></h3>
                <p><?= __('colony_total_plots') ?></p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-success text-white">
                <h3><?= intval($stats['available'] ?? 0) ?></h3>
                <p><?= __('colony_plots_available') ?></p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-warning text-white">
                <h3><?= intval($stats['booked'] ?? 0) ?></h3>
                <p><?= __('colony_plots_booked') ?></p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-danger text-white">
                <h3><?= intval($stats['sold'] ?? 0) ?></h3>
                <p><?= __('colony_plots_sold') ?></p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-info text-white">
                <h3>₹<?= number_format(intval($stats['min_price'] ?? 0)) ?></h3>
                <p><?= __('colony_plots_min_price') ?></p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-secondary text-white">
                <h3>₹<?= number_format(intval($stats['max_price'] ?? 0)) ?></h3>
                <p><?= __('colony_plots_max_price') ?></p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <form method="GET" class="row g-3 align-items-end" id="filterForm">
            <!-- Search -->
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-search"></i> <?= __('colony_search_plot') ?></label>
                <input type="text" name="q" class="form-control" placeholder="<?= __('colony_search_plot_placeholder') ?>" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
            <!-- Dimension Filter -->
            <div class="col-12">
                <label class="fw-semibold mb-2"><?= __('colony_plot_size_label') ?></label>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="dimension-btn <?= empty($current_dimension) ? 'active' : '' ?>" onclick="setFilter('dimension', '')"><?= __('colony_all_sizes') ?></button>
                    <?php foreach ($dimensions as $d): ?>
                        <?php $dim = $d['dimension_label'] ?? ''; ?>
                        <?php if ($dim): ?>
                        <button type="button" class="dimension-btn <?= $current_dimension === $dim ? 'active' : '' ?>" onclick="setFilter('dimension', '<?= htmlspecialchars($dim ?? '') ?>')">
                            <?= htmlspecialchars($dim ?? '') ?> sqft
                        </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Block Filter -->
            <div class="col-md-3">
                <label class="form-label"><?= __('colony_block_label') ?></label>
                <select name="block" class="form-select" onchange="this.form.submit()">
                    <option value=""><?= __('colony_all_blocks') ?></option>
                    <?php foreach ($blocks as $b): ?>
                        <?php $blk = $b['block'] ?? ''; ?>
                        <option value="<?= htmlspecialchars($blk ?? '') ?>" <?= $current_block === $blk ? 'selected' : '' ?>>
                            <?= sprintf(__('colony_block_prefix'), htmlspecialchars($blk ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <label class="form-label"><?= __('colony_status_label') ?></label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="available" <?= $current_status === 'available' ? 'selected' : '' ?>><?= __('colony_status_available') ?></option>
                    <option value="booked" <?= $current_status === 'booked' ? 'selected' : '' ?>><?= __('colony_status_booked') ?></option>
                    <option value="sold" <?= $current_status === 'sold' ? 'selected' : '' ?>><?= __('colony_status_sold') ?></option>
                    <option value="hold" <?= $current_status === 'hold' ? 'selected' : '' ?>><?= __('colony_status_hold') ?></option>
                </select>
            </div>

            <!-- Price Range -->
            <div class="col-md-3">
                <label class="form-label"><?= __('colony_price_range') ?></label>
                <div class="input-group">
                    <input type="number" name="min_price" class="form-control" placeholder="Min ₹" value="<?= $current_min_price > 0 ? $current_min_price : '' ?>">
                    <input type="number" name="max_price" class="form-control" placeholder="Max ₹" value="<?= $current_max_price > 0 ? $current_max_price : '' ?>">
                </div>
            </div>

            <!-- Sort -->
            <div class="col-md-2">
                <label class="form-label"><?= __('colony_sort_by') ?></label>
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="plot_number" <?= $current_sort === 'plot_number' ? 'selected' : '' ?>><?= __('colony_sort_plot_no') ?></option>
                    <option value="price_asc" <?= $current_sort === 'price_asc' ? 'selected' : '' ?>><?= __('colony_sort_price_low') ?></option>
                    <option value="price_desc" <?= $current_sort === 'price_desc' ? 'selected' : '' ?>><?= __('colony_sort_price_high') ?></option>
                    <option value="area_asc" <?= $current_sort === 'area_asc' ? 'selected' : '' ?>><?= __('colony_sort_area_small') ?></option>
                    <option value="area_desc" <?= $current_sort === 'area_desc' ? 'selected' : '' ?>><?= __('colony_sort_area_large') ?></option>
                </select>
            </div>

            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($colony['slug'] ?? '') ?>/plots" class="btn btn-outline-secondary w-100"><?= __('colony_reset_filters') ?></a>
            </div>
        </form>
    </div>

    <!-- Plots Grid -->
    <?php if (empty($plots)): ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4><?= __('colony_no_plots_found') ?></h4>
            <p class="text-muted"><?= __('colony_no_plots_hint') ?></p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($plots as $plot): ?>
                <?php 
                    $statusColor = match($plot['status'] ?? 'available') {
                        'available' => 'success',
                        'booked' => 'warning',
                        'sold' => 'danger',
                        'hold' => 'secondary',
                        'reserved' => 'info',
                        default => 'secondary'
                    };
                    $statusLabel = match($plot['status'] ?? 'available') {
                        'available' => __('colony_status_available'),
                        'booked' => __('colony_status_booked'),
                        'sold' => __('colony_status_sold'),
                        'hold' => __('colony_status_on_hold'),
                        'reserved' => __('colony_status_reserved'),
                        default => ucfirst($plot['status'] ?? 'available')
                    };
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="plot-card position-relative">
                        <span class="badge bg-<?= $statusColor ?> status-badge px-3 py-2"><?= $statusLabel ?></span>
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="plot-number"><?= htmlspecialchars($plot['plot_number'] ?? '') ?></span>
                                    <?php if (!empty($plot['block'])): ?>
                                        <span class="badge bg-light text-dark ms-1"><?= sprintf(__('colony_block_prefix'), htmlspecialchars($plot['block'] ?? '')) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="plot-price">₹<?= number_format(intval($plot['total_price'] ?? 0)) ?></span>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6 plot-detail">
                                    <i class="fas fa-vector-square text-primary"></i> 
                                    <?= number_format(floatval($plot['area_sqft'] ?? 0)) ?> sqft
                                </div>
                                <div class="col-6 plot-detail">
                                    <i class="fas fa-arrows-alt text-primary"></i> 
                                    <?= htmlspecialchars($plot['dimension_label'] ?? '') ?>
                                </div>
                                <div class="col-6 plot-detail">
                                    <i class="fas fa-rupee-sign text-primary"></i> 
                                    ₹<?= number_format(floatval($plot['price_per_sqft'] ?? 0)) ?>/sqft
                                </div>
                                <div class="col-6 plot-detail">
                                    <?php if ($plot['corner_plot'] ?? false): ?>
                                        <span class="amenity-tag"><?= __('colony_corner_plot') ?></span>
                                    <?php endif; ?>
                                    <?php if ($plot['park_facing'] ?? false): ?>
                                        <span class="amenity-tag"><?= __('colony_park_facing') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <?php if (($plot['status'] ?? '') === 'available'): ?>
                                    <a href="<?= BASE_URL ?>/plot/<?= $plot['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                        <i class="fas fa-info-circle"></i> <?= __('colony_view_plot') ?>
                                    </a>
                                    <button class="btn btn-sm btn-compare" data-id="<?= $plot['id'] ?>" onclick="addToCompare(<?= $plot['id'] ?>)" title="Compare">
                                        <i class="fas fa-balance-scale"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-id="<?= $plot['id'] ?>" title="Favourite" onclick="togglePlotFav(this)">
                                        <i class="far fa-heart"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary w-100" disabled>
                                        <i class="fas fa-lock"></i> <?= __('colony_not_available') ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Floating Compare Bar -->
<div id="compare-bar" class="compare-bar style-2248">
    <div class="compare-bar-inner">
        <span class="compare-bar-count"><i class="fas fa-balance-scale me-1"></i> <span id="compare-count">0</span> <?= __('colony_plots_selected') ?></span>
        <div class="compare-bar-actions">
            <a href="<?= BASE_URL ?>/compare" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold"><?= __('colony_compare_now') ?></a>
            <button onclick="clearCompare()" class="btn btn-sm btn-outline-light rounded-3 px-3"><?= __('colony_clear') ?></button>
        </div>
    </div>
</div>

<style>
.compare-bar {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999;
    background: linear-gradient(135deg, #1e3a5f, #2563eb);
    color: #fff; padding: 12px 20px;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
    animation: slideUp 0.3s ease;
}
.compare-bar-inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
.compare-bar-count { font-weight: 600; font-size: 0.95rem; }
.compare-bar-actions { display: flex; gap: 8px; }
.btn-compare {
    background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;
    border-radius: 8px; padding: 4px 10px; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;
}
.btn-compare:hover, .btn-compare.active {
    background: #2563eb; color: #fff; border-color: #2563eb;
}
@keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
</style>

<script>
function setFilter(name, value) {
    const form = document.getElementById('filterForm');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
    form.submit();
}

function togglePlotFav(btn) {
    const id = btn.dataset.id;
    if (!id) return;
    const icon = btn.querySelector('i');
    const isFav = icon.classList.contains('fas');
    fetch('<?= BASE_URL ?>/dashboard/favorites/add', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'property_id=' + id
    }).then(r => r.json()).then(d => {
        if (d.success) {
            icon.className = isFav ? 'far fa-heart' : 'fas fa-heart';
        } else if (d.message && d.message.includes('login')) {
            window.location.href = '<?= BASE_URL ?>/login';
        } else {
            icon.className = 'far fa-heart';
        }
    }).catch(() => {});
}

/* --- Compare Feature --- */
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
            refreshCompareBar();
            /* highlight the button */
            document.querySelectorAll('.btn-compare[data-id="' + plotId + '"]').forEach(b => b.classList.add('active'));
        } else {
            alert(d.message);
        }
    });
}

function clearCompare() {
    fetch('<?= BASE_URL ?>/compare/clear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= $csrf_token ?? '' ?>'
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            document.getElementById('compare-bar').style.display = 'none';
            document.querySelectorAll('.btn-compare').forEach(b => b.classList.remove('active'));
        }
    });
}

function refreshCompareBar() {
    fetch('<?= BASE_URL ?>/compare/count')
    .then(r => r.json())
    .then(d => {
        var bar = document.getElementById('compare-bar');
        var count = d.count || 0;
        document.getElementById('compare-count').textContent = count;
        bar.style.display = count > 0 ? 'block' : 'none';
    });
}

/* Load compare bar state on page load */
document.addEventListener('DOMContentLoaded', function() { refreshCompareBar(); });
</script>
