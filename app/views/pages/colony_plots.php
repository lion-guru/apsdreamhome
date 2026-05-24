<?php $colony = $colony ?? []; $plots = $plots ?? []; $dimensions = $dimensions ?? []; $blocks = $blocks ?? []; $stats = $stats ?? []; ?>
<style>
.plot-card { transition: all 0.3s ease; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; }
.plot-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
.plot-card .plot-number { font-size: 1.1rem; font-weight: 700; color: #1a237e; }
.plot-card .plot-price { font-size: 1.3rem; font-weight: 700; color: #2e7d32; }
.plot-card .plot-detail { font-size: 0.9rem; color: #555; }
.plot-card .status-badge { position: absolute; top: 12px; right: 12px; }
.plot-card .amenity-tag { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 0.78rem; background: #e8eaf6; color: #283593; margin: 2px; }
.dimension-btn { padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; border: 1px solid #c5cae9; cursor: pointer; transition: all 0.2s; background: white; color: #333; }
.dimension-btn:hover, .dimension-btn.active { background: #1a237e; color: white; border-color: #1a237e; }
.filter-section { background: #f5f7ff; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
.stat-card { padding: 16px; border-radius: 10px; text-align: center; }
.stat-card h3 { font-size: 1.8rem; font-weight: 800; margin: 0; }
.stat-card p { font-size: 0.85rem; margin: 4px 0 0; opacity: 0.85; }
</style>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/plots">Plots</a></li>
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
                <i class="fas fa-map"></i> View on Map
            </a>
            <a href="<?= BASE_URL ?>/contact" class="btn btn-primary">
                <i class="fas fa-phone"></i> Enquire Now
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4 g-3">
        <div class="col-4 col-md-2">
            <div class="stat-card bg-primary text-white">
                <h3><?= intval($stats['total'] ?? 0) ?></h3>
                <p>Total Plots</p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-success text-white">
                <h3><?= intval($stats['available'] ?? 0) ?></h3>
                <p>Available</p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-warning text-white">
                <h3><?= intval($stats['booked'] ?? 0) ?></h3>
                <p>Booked</p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-danger text-white">
                <h3><?= intval($stats['sold'] ?? 0) ?></h3>
                <p>Sold</p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-info text-white">
                <h3>₹<?= number_format(intval($stats['min_price'] ?? 0)) ?></h3>
                <p>Min Price</p>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="stat-card bg-secondary text-white">
                <h3>₹<?= number_format(intval($stats['max_price'] ?? 0)) ?></h3>
                <p>Max Price</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <form method="GET" class="row g-3 align-items-end" id="filterForm">
            <!-- Search -->
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-search"></i> Search Plot</label>
                <input type="text" name="q" class="form-control" placeholder="Plot number, area, or features..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
            <!-- Dimension Filter -->
            <div class="col-12">
                <label class="fw-semibold mb-2">Plot Size (width x length)</label>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="dimension-btn <?= empty($current_dimension) ? 'active' : '' ?>" onclick="setFilter('dimension', '')">All Sizes</button>
                    <?php foreach ($dimensions as $d): ?>
                        <?php $dim = $d['dimension_label'] ?? ''; ?>
                        <?php if ($dim): ?>
                        <button type="button" class="dimension-btn <?= $current_dimension === $dim ? 'active' : '' ?>" onclick="setFilter('dimension', '<?= htmlspecialchars($dim) ?>')">
                            <?= htmlspecialchars($dim) ?> sqft
                        </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Block Filter -->
            <div class="col-md-3">
                <label class="form-label">Block</label>
                <select name="block" class="form-select" onchange="this.form.submit()">
                    <option value="">All Blocks</option>
                    <?php foreach ($blocks as $b): ?>
                        <?php $blk = $b['block'] ?? ''; ?>
                        <option value="<?= htmlspecialchars($blk) ?>" <?= $current_block === $blk ? 'selected' : '' ?>>
                            Block <?= htmlspecialchars($blk) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="available" <?= $current_status === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="booked" <?= $current_status === 'booked' ? 'selected' : '' ?>>Booked</option>
                    <option value="sold" <?= $current_status === 'sold' ? 'selected' : '' ?>>Sold</option>
                    <option value="hold" <?= $current_status === 'hold' ? 'selected' : '' ?>>Hold</option>
                </select>
            </div>

            <!-- Price Range -->
            <div class="col-md-3">
                <label class="form-label">Price Range</label>
                <div class="input-group">
                    <input type="number" name="min_price" class="form-control" placeholder="Min ₹" value="<?= $current_min_price > 0 ? $current_min_price : '' ?>">
                    <input type="number" name="max_price" class="form-control" placeholder="Max ₹" value="<?= $current_max_price > 0 ? $current_max_price : '' ?>">
                </div>
            </div>

            <!-- Sort -->
            <div class="col-md-2">
                <label class="form-label">Sort By</label>
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="plot_number" <?= $current_sort === 'plot_number' ? 'selected' : '' ?>>Plot No.</option>
                    <option value="price_asc" <?= $current_sort === 'price_asc' ? 'selected' : '' ?>>Price: Low</option>
                    <option value="price_desc" <?= $current_sort === 'price_desc' ? 'selected' : '' ?>>Price: High</option>
                    <option value="area_asc" <?= $current_sort === 'area_asc' ? 'selected' : '' ?>>Area: Small</option>
                    <option value="area_desc" <?= $current_sort === 'area_desc' ? 'selected' : '' ?>>Area: Large</option>
                </select>
            </div>

            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($colony['slug'] ?? '') ?>/plots" class="btn btn-outline-secondary w-100">Reset Filters</a>
            </div>
        </form>
    </div>

    <!-- Plots Grid -->
    <?php if (empty($plots)): ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4>No Plots Found</h4>
            <p class="text-muted">Try adjusting your filters or check back later for new listings.</p>
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
                        'available' => 'Available',
                        'booked' => 'Booked',
                        'sold' => 'Sold',
                        'hold' => 'On Hold',
                        'reserved' => 'Reserved',
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
                                        <span class="badge bg-light text-dark ms-1">Block <?= htmlspecialchars($plot['block']) ?></span>
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
                                        <span class="amenity-tag">Corner</span>
                                    <?php endif; ?>
                                    <?php if ($plot['park_facing'] ?? false): ?>
                                        <span class="amenity-tag">Park Facing</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <?php if (($plot['status'] ?? '') === 'available'): ?>
                                    <a href="<?= BASE_URL ?>/plot/<?= $plot['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                        <i class="fas fa-info-circle"></i> View Details
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" data-id="<?= $plot['id'] ?>" title="Favourite" onclick="togglePlotFav(this)">
                                        <i class="far fa-heart"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary w-100" disabled>
                                        <i class="fas fa-lock"></i> Not Available
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
    fetch(<?= json_encode(BASE_URL . '/dashboard/favorites/' . (isset($_SESSION['user_id']) ? 'add' : 'add')) ?>, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'property_id=' + id
    }).then(r => r.json()).then(d => {
        if (d.success) {
            icon.className = isFav ? 'far fa-heart' : 'fas fa-heart';
        } else if (d.message.includes('login')) {
            window.location.href = <?= json_encode(BASE_URL . '/login') ?>;
        } else {
            icon.className = 'far fa-heart';
        }
    }).catch(() => {});
}
</script>
