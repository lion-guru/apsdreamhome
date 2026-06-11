<?php
$page_title = $page_title ?? 'Properties - APS Dream Home';
$current_page = 'properties';

// Pull current filter values from URL ($_GET) with safe defaults
$currentFilters = [];
foreach (['q','type','listing','location','min_price','max_price','bedrooms','bathrooms','furnished','year_built','area_min','area_max','sort'] as $k) {
    $currentFilters[$k] = $_GET[$k] ?? '';
}
$hasActiveFilters = false;
foreach ($currentFilters as $k => $v) {
    if ($v !== '' && $v !== null && $v !== 0 && $k !== 'sort') {
        $hasActiveFilters = true; break;
    }
}

// Build JSON-LD ItemList of properties for SEO structured data
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Properties - APS Dream Home',
    'description' => 'Browse ' . number_format($total ?? 0) . ' premium properties from APS Dream Home',
    'url' => (defined('BASE_URL') ? BASE_URL : '') . '/properties',
    'numberOfItems' => (int)($total ?? 0),
    'itemListElement' => []
];
if (!empty($properties) && is_array($properties)) {
    $startPosition = (int)((($page ?? 1) - 1) * 12) + 1; // matches $perPage in controller
    foreach ($properties as $i => $property) {
        $itemUrl = (defined('BASE_URL') ? BASE_URL : '') . '/property/' . ($property['id'] ?? '');
        $itemImage = !empty($property['image'])
            ? ((defined('BASE_URL') ? BASE_URL : '') . '/assets/images/properties/' . $property['image'])
            : '';
        $jsonLd['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $startPosition + $i,
            'item' => [
                '@type' => 'RealEstateListing',
                'name' => $property['name'] ?? $property['title'] ?? 'Property',
                'url' => $itemUrl,
                'image' => $itemImage,
                'description' => $property['description'] ?? '',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $property['address'] ?? $property['location'] ?? ''
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'price' => (float)($property['price'] ?? 0),
                    'priceCurrency' => 'INR',
                    'availability' => 'https://schema.org/InStock'
                ]
            ]
        ];
    }
}
// Pass JSON-LD to layout by injecting it into the $seo array (which was
// already extracted from $data['seo'] in BaseController::render() before
// this view file ran). Modifying $seo here makes the JSON-LD visible in
// base.php / header.php <head>.
$seo = is_array($seo ?? null) ? $seo : [];
$seo['json_ld'] = $jsonLd;

// SEO description / keywords (used by BaseController::generateSEO fallback)
$meta_description = 'Browse ' . number_format($total ?? 0) . ' premium properties — plots, flats, villas, farmhouses — from APS Dream Home across India. Verified listings, transparent pricing, RERA compliant.';
$meta_keywords = 'real estate, properties, plots, flats, villas, farmhouses, ' . implode(', ', array_filter([
    $currentFilters['location'] ?? null,
    $currentFilters['type'] ?? null
]));
?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>"><?= __('home') ?></a></li>
            <li class="breadcrumb-item active"><?= __('properties') ?></li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-7">
            <h1 class="display-6 fw-bold text-primary mb-1">
                <i class="fas fa-building me-2"></i><?= __('properties') ?>
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-list me-1"></i>
                <strong id="resultsCount"><?= number_format($total ?? 0) ?></strong> <?= __('properties_found') ?>
                <?php if ($hasActiveFilters): ?>
                    <span class="badge bg-primary-subtle text-primary ms-2">Filtered</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <div class="btn-group" role="group" aria-label="View toggle">
                <button type="button" class="btn btn-outline-primary active" id="gridViewBtn" onclick="setView('grid')">
                    <i class="fas fa-th-large me-1"></i>Grid
                </button>
                <button type="button" class="btn btn-outline-primary" id="mapViewBtn" onclick="setView('map')" disabled title="Coming soon">
                    <i class="fas fa-map-marked-alt me-1"></i>Map <span class="badge bg-secondary ms-1">Soon</span>
                </button>
            </div>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <button type="button" class="btn btn-success rounded-pill ms-2" onclick="triggerSaveSearch()" id="saveSearchBtnInline" style="<?= $hasActiveFilters ? '' : 'display:none;' ?>">
                    <i class="fas fa-bookmark me-1"></i>Save Search
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Saved Searches Dropdown (only for logged-in users) -->
    <?php if (!empty($_SESSION['user_id'])): ?>
        <?php $savedSearches = $savedSearches ?? []; ?>
        <?php $isLoggedIn = true; ?>
        <?php include __DIR__ . '/../components/saved_search_dropdown.php'; ?>
    <?php endif; ?>

    <!-- Advanced Search Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-sliders-h text-primary me-2"></i>Advanced Search
                <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="true">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </h6>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo me-1"></i>Reset
                </button>
            </div>
        </div>
        <div class="card-body collapse show" id="advancedFilters">
            <form method="GET" action="<?php echo BASE_URL; ?>/properties" id="propertyFilterForm" class="row g-3">
                <!-- Text Search -->
                <div class="col-md-4">
                    <label for="q" class="form-label small fw-semibold"><i class="fas fa-search"></i> Keyword</label>
                    <input type="text" class="form-control" id="q" name="q" placeholder="Property name, address, description..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>

                <!-- Property Type -->
                <div class="col-md-2">
                    <label for="type" class="form-label small fw-semibold"><?= __('filter_type') ?></label>
                    <select class="form-select form-select-sm" id="type" name="type">
                        <option value="">All Types</option>
                        <option value="plot" <?= ($_GET['type'] ?? '') === 'plot' ? 'selected' : ''; ?>>Plot</option>
                        <option value="house" <?= ($_GET['type'] ?? '') === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="flat" <?= ($_GET['type'] ?? '') === 'flat' ? 'selected' : ''; ?>>Flat/Apartment</option>
                        <option value="shop" <?= ($_GET['type'] ?? '') === 'shop' ? 'selected' : ''; ?>>Shop</option>
                        <option value="farmhouse" <?= ($_GET['type'] ?? '') === 'farmhouse' ? 'selected' : ''; ?>>Farmhouse</option>
                        <option value="villa" <?= ($_GET['type'] ?? '') === 'villa' ? 'selected' : ''; ?>>Villa</option>
                        <option value="land" <?= ($_GET['type'] ?? '') === 'land' ? 'selected' : ''; ?>>Land</option>
                    </select>
                </div>

                <!-- Listing Type -->
                <div class="col-md-2">
                    <label for="listing" class="form-label small fw-semibold"><?= __('filter_listing') ?></label>
                    <select class="form-select form-select-sm" id="listing" name="listing">
                        <option value="">Buy & Rent</option>
                        <option value="sell" <?= ($_GET['listing'] ?? '') === 'sell' ? 'selected' : ''; ?>>For Sale</option>
                        <option value="rent" <?= ($_GET['listing'] ?? '') === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                    </select>
                </div>

                <!-- Location -->
                <div class="col-md-2">
                    <label for="location" class="form-label small fw-semibold"><?= __('filter_location') ?></label>
                    <select class="form-select form-select-sm" id="location" name="location">
                        <option value="">All Locations</option>
                        <?php foreach (($locations ?? []) as $loc): ?>
                            <option value="<?= htmlspecialchars($loc) ?>" <?= ($_GET['location'] ?? '') === $loc ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($loc) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sort -->
                <div class="col-md-2">
                    <label for="sort" class="form-label small fw-semibold">Sort By</label>
                    <select class="form-select form-select-sm" id="sort" name="sort" onchange="document.getElementById('propertyFilterForm').submit()">
                        <option value="newest" <?= ($_GET['sort'] ?? 'newest') === 'newest' ? 'selected' : ''; ?>><i class="fas fa-clock"></i> Newest First</option>
                        <option value="oldest" <?= ($_GET['sort'] ?? '') === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="relevance" <?= ($_GET['sort'] ?? '') === 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                        <option value="price_low" <?= ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?= ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="area_large" <?= ($_GET['sort'] ?? '') === 'area_large' ? 'selected' : ''; ?>>Area: Largest First</option>
                        <option value="area_small" <?= ($_GET['sort'] ?? '') === 'area_small' ? 'selected' : ''; ?>>Area: Smallest First</option>
                    </select>
                </div>

                <!-- Advanced fields row -->
                <div class="col-md-2">
                    <label for="bedrooms" class="form-label small fw-semibold">Bedrooms (min)</label>
                    <select class="form-select form-select-sm" id="bedrooms" name="bedrooms">
                        <option value="">Any</option>
                        <option value="1" <?= ($_GET['bedrooms'] ?? '') === '1' ? 'selected' : ''; ?>>1+ BHK</option>
                        <option value="2" <?= ($_GET['bedrooms'] ?? '') === '2' ? 'selected' : ''; ?>>2+ BHK</option>
                        <option value="3" <?= ($_GET['bedrooms'] ?? '') === '3' ? 'selected' : ''; ?>>3+ BHK</option>
                        <option value="4" <?= ($_GET['bedrooms'] ?? '') === '4' ? 'selected' : ''; ?>>4+ BHK</option>
                        <option value="5" <?= ($_GET['bedrooms'] ?? '') === '5' ? 'selected' : ''; ?>>5+ BHK</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="bathrooms" class="form-label small fw-semibold">Bathrooms (min)</label>
                    <select class="form-select form-select-sm" id="bathrooms" name="bathrooms">
                        <option value="">Any</option>
                        <option value="1" <?= ($_GET['bathrooms'] ?? '') === '1' ? 'selected' : ''; ?>>1+</option>
                        <option value="2" <?= ($_GET['bathrooms'] ?? '') === '2' ? 'selected' : ''; ?>>2+</option>
                        <option value="3" <?= ($_GET['bathrooms'] ?? '') === '3' ? 'selected' : ''; ?>>3+</option>
                        <option value="4" <?= ($_GET['bathrooms'] ?? '') === '4' ? 'selected' : ''; ?>>4+</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="furnished" class="form-label small fw-semibold">Furnished</label>
                    <select class="form-select form-select-sm" id="furnished" name="furnished">
                        <option value="">Any</option>
                        <option value="unfurnished" <?= ($_GET['furnished'] ?? '') === 'unfurnished' ? 'selected' : ''; ?>>Unfurnished</option>
                        <option value="semi-furnished" <?= ($_GET['furnished'] ?? '') === 'semi-furnished' ? 'selected' : ''; ?>>Semi-Furnished</option>
                        <option value="fully-furnished" <?= ($_GET['furnished'] ?? '') === 'fully-furnished' ? 'selected' : ''; ?>>Fully-Furnished</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="year_built" class="form-label small fw-semibold">Year Built (â‰¥)</label>
                    <select class="form-select form-select-sm" id="year_built" name="year_built">
                        <option value="">Any</option>
                        <?php for ($y = (int)date('Y'); $y >= 2000; $y--): ?>
                            <option value="<?= $y ?>" <?= ($_GET['year_built'] ?? '') == (string)$y ? 'selected' : ''; ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="area_min" class="form-label small fw-semibold">Min Area (sqft)</label>
                    <input type="number" class="form-control form-control-sm" id="area_min" name="area_min" placeholder="e.g. 500" min="0" value="<?= htmlspecialchars($_GET['area_min'] ?? '') ?>">
                </div>

                <div class="col-md-2">
                    <label for="area_max" class="form-label small fw-semibold">Max Area (sqft)</label>
                    <input type="number" class="form-control form-control-sm" id="area_max" name="area_max" placeholder="e.g. 5000" min="0" value="<?= htmlspecialchars($_GET['area_max'] ?? '') ?>">
                </div>

                <!-- Price range -->
                <div class="col-md-2">
                    <label for="min_price" class="form-label small fw-semibold"><?= __('min_price') ?> (&#8377;)</label>
                    <select class="form-select form-select-sm" id="min_price" name="min_price">
                        <option value="">No Min</option>
                        <?php foreach ([100000=>'1L', 500000=>'5L', 1000000=>'10L', 2000000=>'20L', 5000000=>'50L', 10000000=>'1Cr', 20000000=>'2Cr'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($_GET['min_price'] ?? '') == (string)$val ? 'selected' : ''; ?>>&#8377;<?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="max_price" class="form-label small fw-semibold"><?= __('max_price') ?> (&#8377;)</label>
                    <select class="form-select form-select-sm" id="max_price" name="max_price">
                        <option value="">No Max</option>
                        <?php foreach ([500000=>'5L', 1000000=>'10L', 2000000=>'20L', 5000000=>'50L', 10000000=>'1Cr', 20000000=>'2Cr', 50000000=>'5Cr'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($_GET['max_price'] ?? '') == (string)$val ? 'selected' : ''; ?>>&#8377;<?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2 align-items-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear All
                    </a>
                    <?php if (!empty($_SESSION['user_id']) && $hasActiveFilters): ?>
                        <button type="button" class="btn btn-success ms-auto" onclick="triggerSaveSearch()">
                            <i class="fas fa-bookmark me-1"></i>Save this search
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="row" id="propertiesContainer" data-experiment="property_card_layout" data-variant="<?= htmlspecialchars($_SESSION['experiments']['property_card_layout'] ?? 'current', ENT_QUOTES) ?>">
        <?php
            // A/B test: property_card_layout — 'compact' variant = 4 per row (col-lg-3)
            $cardLayout = $_SESSION['experiments']['property_card_layout'] ?? 'current';
            $cardColClass = $cardLayout === 'compact' ? 'col-lg-3 col-md-6' : 'col-lg-4 col-md-6';
            $cardClass    = $cardLayout === 'compact' ? 'property-card property-card-compact h-100' : 'property-card h-100';
        ?>
        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $property): ?>
                <div class="<?= htmlspecialchars($cardColClass) ?> mb-4">
                    <div class="card <?= htmlspecialchars($cardClass) ?>" data-gallery="property-card-<?= (int)($property['id'] ?? 0) ?>" data-property-id="<?= (int)($property['id'] ?? 0) ?>" data-property-track="property_card">
                        <div class="position-relative">
                            <?php
                                $imgSrc = BASE_URL . '/assets/images/properties/' . htmlspecialchars($property['image'] ?? '');
                                if (empty($property['image'])) {
                                    $imgSrc = BASE_URL . '/assets/images/placeholder/property.svg';
                                }
                            ?>
                            <img loading="lazy" src="<?= $imgSrc ?>"
                                 class="card-img-top property-image"
                                 alt="<?= htmlspecialchars($property['name'] ?? 'Property image') ?>"
                                 data-caption="<?= htmlspecialchars($property['name'] ?? '') ?>"
                                 style="height: 200px; object-fit: cover; cursor: zoom-in;"
                                 onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                            <div class="position-absolute top-0 end-0 p-2 d-flex gap-1">
                                <button class="btn btn-sm btn-light favorite-btn" data-id="<?= $property['id'] ?? '' ?>" title="Add to Favorites" onclick="toggleFavorite(this)">
                                    <i class="far fa-heart text-danger"></i>
                                </button>
                                <span class="badge bg-<?= ($property['listing_type'] ?? 'sell') === 'rent' ? 'info' : 'success'; ?>">
                                    <?= ucfirst($property['listing_type'] ?? 'Sell') ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($property['name'] ?? '') ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['address'] ?? $property['location'] ?? '') ?>
                            </p>
                            <p class="card-text small"><?= htmlspecialchars(substr($property['description'] ?? '', 0, 100)) ?>...</p>
                            <div class="row small text-center border-top border-bottom py-2 mb-3 g-1">
                                <div class="col">
                                    <i class="fas fa-vector-square text-muted"></i><br>
                                    <strong><?= number_format((float)($property['area_sqft'] ?? 0)) ?></strong> sq ft
                                </div>
                                <div class="col">
                                    <i class="fas fa-home text-muted"></i><br>
                                    <strong><?= ucfirst($property['property_type'] ?? 'Plot') ?></strong>
                                </div>
                                <?php if (!empty($property['bedrooms'])): ?>
                                <div class="col">
                                    <i class="fas fa-bed text-muted"></i><br>
                                    <strong><?= (int)$property['bedrooms'] ?></strong> BHK
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($property['furnished'])): ?>
                                <div class="col">
                                    <i class="fas fa-couch text-muted"></i><br>
                                    <strong><?= ucfirst($property['furnished']) ?></strong>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-success fw-bold fs-5">&#8377;<?= number_format((float)($property['price'] ?? 0)) ?></span>
                                    <?php if (($property['listing_type'] ?? 'sell') === 'rent'): ?>
                                        <span class="text-muted">/month</span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="<?= BASE_URL ?>/contact" class="btn btn-sm btn-primary">
                                        <i class="fas fa-phone"></i> Enquire
                                    </a>
                                    <button class="btn btn-sm btn-outline-info add-to-compare" data-id="<?= $property['id'] ?? '' ?>" onclick="addToCompare(this)" title="Add to compare">
                                        <i class="fas fa-balance-scale"></i>
                                    </button>
                                </div>
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
                        <a href="<?= BASE_URL ?>/properties" class="btn btn-primary"><?= __('view_all') ?> Properties</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 0) > 1): ?>
        <?php
        // Build pagination URL preserving all current filters
        $paginationParams = $_GET;
        unset($paginationParams['page']);
        ?>
        <nav aria-label="Property pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if (($page ?? 1) > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($paginationParams, ['page' => ($page - 1)])) ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>
                <?php endif; ?>

                <?php
                $startPage = max(1, ($page ?? 1) - 2);
                $endPage = min($totalPages, ($page ?? 1) + 2);
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($paginationParams, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if (($page ?? 1) < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($paginationParams, ['page' => ($page + 1)])) ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Save Search Modal -->
<?php include __DIR__ . '/../components/save_search_modal.php'; ?>

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
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
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
        } else if (d.message && d.message.includes('login')) {
            window.location.href = BASE_URL + '/login';
        }
    }).catch(() => {});
}

function addToCompare(btn) {
    const id = btn.dataset.id;
    if (!id) return;
    const fd = new FormData();
    fd.append('property_id', id);
    fetch(BASE_URL + '/property-comparison/add', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
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

function setView(view) {
    if (view === 'map') {
        showToast('Map view coming soon — use grid view to browse', 'info');
        return;
    }
    document.getElementById('gridViewBtn').classList.add('active');
    document.getElementById('mapViewBtn').classList.remove('active');
}

function resetFilters() {
    window.location.href = BASE_URL + '/properties';
}

document.addEventListener('DOMContentLoaded', updateCompareBadge);
</script>
