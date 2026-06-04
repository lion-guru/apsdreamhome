

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>"><?= __('home') ?></a></li>
            <li class="breadcrumb-item active"><?= __('properties') ?></li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-building me-2"></i><?= __('properties') ?>
            </h1>
            <p class="text-muted"><?php echo number_format($total); ?> <?= __('properties_found') ?></p>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>/properties" class="row g-3">
                <div class="col-md-3">
                    <label for="q" class="form-label"><i class="fas fa-search"></i> <?= __('search') ?></label>
                    <input type="text" class="form-control" id="q" name="q" placeholder="<?= __('search_placeholder') ?>" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label"><?= __('filter_type') ?></label>
                    <select class="form-select" id="type" name="type">
                        <option value=""><?= __('all_types') ?></option>
                        <option value="plot" <?php echo $type === 'plot' ? 'selected' : ''; ?>>Plot</option>
                        <option value="house" <?php echo $type === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="flat" <?php echo $type === 'flat' ? 'selected' : ''; ?>>Flat/Apartment</option>
                        <option value="shop" <?php echo $type === 'shop' ? 'selected' : ''; ?>>Shop</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="listing" class="form-label"><?= __('filter_listing') ?></label>
                    <select class="form-select" id="listing" name="listing">
                        <option value=""><?= __('buy') ?> & <?= __('rent') ?></option>
                        <option value="sell" <?php echo $listingType === 'sell' ? 'selected' : ''; ?>>For Sale</option>
                        <option value="rent" <?php echo $listingType === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="location" class="form-label"><?= __('filter_location') ?></label>
                    <select class="form-select" id="location" name="location">
                        <option value=""><?= __('all_locations') ?></option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo $location === $loc ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort" class="form-label"><?= __('filter_sort') ?></label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>><?= __('newest_first') ?></option>
                        <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>><?= __('price_low_high') ?></option>
                        <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>><?= __('price_high_low') ?></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="min_price" class="form-label"><?= __('min_price') ?> (₹)</label>
                    <select class="form-select" id="min_price" name="min_price">
                        <option value="">No Min</option>
                        <option value="100000" <?php echo ($minPrice ?? 0) == 100000 ? 'selected' : ''; ?>>₹1 Lakh</option>
                        <option value="500000" <?php echo ($minPrice ?? 0) == 500000 ? 'selected' : ''; ?>>₹5 Lakhs</option>
                        <option value="1000000" <?php echo ($minPrice ?? 0) == 1000000 ? 'selected' : ''; ?>>₹10 Lakhs</option>
                        <option value="2000000" <?php echo ($minPrice ?? 0) == 2000000 ? 'selected' : ''; ?>>₹20 Lakhs</option>
                        <option value="5000000" <?php echo ($minPrice ?? 0) == 5000000 ? 'selected' : ''; ?>>₹50 Lakhs</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="max_price" class="form-label"><?= __('max_price') ?> (₹)</label>
                    <select class="form-select" id="max_price" name="max_price">
                        <option value="">No Max</option>
                        <option value="500000" <?php echo ($maxPrice ?? 0) == 500000 ? 'selected' : ''; ?>>₹5 Lakhs</option>
                        <option value="1000000" <?php echo ($maxPrice ?? 0) == 1000000 ? 'selected' : ''; ?>>₹10 Lakhs</option>
                        <option value="2000000" <?php echo ($maxPrice ?? 0) == 2000000 ? 'selected' : ''; ?>>₹20 Lakhs</option>
                        <option value="5000000" <?php echo ($maxPrice ?? 0) == 5000000 ? 'selected' : ''; ?>>₹50 Lakhs</option>
                        <option value="10000000" <?php echo ($maxPrice ?? 0) == 10000000 ? 'selected' : ''; ?>>₹1 Crore</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> <?= __('search') ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-secondary"><?= __('clear_filters') ?></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="row">
        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $property): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card property-card h-100">
                        <div class="position-relative">
                            <?php
                                $imgSrc = BASE_URL . '/assets/images/properties/' . htmlspecialchars($property['image'] ?? '');
                                if (empty($property['image'])) {
                                    $imgSrc = BASE_URL . '/assets/images/placeholder/property.svg';
                                }
                            ?>
                            <img src="<?php echo $imgSrc; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($property['name']); ?>"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg'">
                            <div class="position-absolute top-0 end-0 p-2 d-flex gap-1">
                                <button class="btn btn-sm btn-light favorite-btn" data-id="<?= $property['id'] ?? '' ?>" title="Add to Favorites" onclick="toggleFavorite(this)">
                                    <i class="far fa-heart text-danger"></i>
                                </button>
                                <span class="badge bg-<?php echo ($property['listing_type'] ?? 'sell') === 'rent' ? 'info' : 'success'; ?>">
                                    <?php echo ucfirst($property['listing_type'] ?? 'Sell'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($property['name']); ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['address'] ?? $property['location']); ?>
                            </p>
                            <p class="card-text small"><?php echo htmlspecialchars(substr($property['description'] ?? '', 0, 100)); ?>...</p>
                            <div class="row small text-center border-top border-bottom py-2 mb-3">
                                <div class="col-4">
                                    <i class="fas fa-vector-square text-muted"></i><br>
                                    <strong><?php echo number_format($property['area_sqft'] ?? 0); ?></strong> sq ft
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-home text-muted"></i><br>
                                    <strong><?php echo ucfirst($property['property_type'] ?? 'Plot'); ?></strong>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-eye text-muted"></i><br>
                                    <strong><?php echo $property['views'] ?? 0; ?></strong> views
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-success fw-bold fs-5">₹<?php echo number_format($property['price']); ?></span>
                                    <?php if (($property['listing_type'] ?? 'sell') === 'rent'): ?>
                                        <span class="text-muted">/month</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-primary btn-sm">
                                    <i class="fas fa-phone"></i> <?= __('enquire') ?>
                                </a>
                                <button class="btn btn-sm btn-outline-info add-to-compare" data-id="<?= $property['id'] ?? '' ?>" onclick="addToCompare(this)">
                                    <i class="fas fa-balance-scale me-1"></i> <?= __('compare') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted"><?= __('no_properties') ?></h5>
                        <p class="text-muted"><?= __('no_results_tip') ?></p>
                        <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-primary"><?= __('view_all') ?> <?= __('properties') ?></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Property pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo urlencode($type); ?>&listing=<?php echo urlencode($listingType); ?>&location=<?php echo urlencode($location); ?>&sort=<?php echo urlencode($sortBy); ?>">
                            <?= __('previous') ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i >= $page - 2 && $i <= $page + 2): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo urlencode($type); ?>&listing=<?php echo urlencode($listingType); ?>&location=<?php echo urlencode($location); ?>&sort=<?php echo urlencode($sortBy); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo urlencode($type); ?>&listing=<?php echo urlencode($listingType); ?>&location=<?php echo urlencode($location); ?>&sort=<?php echo urlencode($sortBy); ?>">
                            <?= __('next') ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<style>
.property-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}
.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.breadcrumb {
    background: transparent;
    padding: 0;
}
</style>
<script>
function toggleFavorite(btn) {
    const id = btn.dataset.id;
    if (!id) return;
    const icon = btn.querySelector('i');
    const isFav = icon.classList.contains('fas');
    const url = isFav ? BASE_URL + '/dashboard/favorites/remove' : BASE_URL + '/dashboard/favorites/add';
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'property_id=' + id
    }).then(r => r.json()).then(d => {
        if (d.success) {
            if (isFav) {
                icon.className = 'far fa-heart text-danger';
                btn.title = 'Add to Favorites';
            } else {
                icon.className = 'fas fa-heart text-danger';
                btn.title = 'Remove from Favorites';
            }
        } else if (d.message.includes('login')) {
            window.location.href = BASE_URL + '/login';
        }
    }).catch(() => {});
}
function addToCompare(btn) {
    const id = btn.dataset.id;
    if (!id) return;
    const fd = new FormData();
    fd.append('property_id', id);
    fetch(BASE_URL + '/property-comparison/add', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }})
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Added';
                btn.classList.remove('btn-outline-info');
                btn.classList.add('btn-info');
                updateCompareBadge(d.count);
            } else {
                alert(d.error || 'Failed to add to comparison');
            }
        }).catch(() => alert('Network error'));
}
function updateCompareBadge(count) {
    const badge = document.getElementById('compareBadge');
    if (badge) {
        if (count === undefined) {
            fetch(BASE_URL + '/property-comparison', { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                .then(() => {
                    let stored = parseInt(localStorage.getItem('property_compare_count') || '0');
                    badge.textContent = stored;
                    badge.style.display = stored > 0 ? 'inline' : 'none';
                }).catch(() => {
                    badge.textContent = 0;
                    badge.style.display = 'none';
                });
        } else {
            localStorage.setItem('property_compare_count', count);
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }
    }
}
document.addEventListener('DOMContentLoaded', updateCompareBadge);
</script>

