<?php
$page_title = $page_title ?? 'Properties - APS Dream Home';
$current_page = 'properties';

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
    $startPosition = (int)((($page ?? 1) - 1) * 12) + 1;
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
$seo = is_array($seo ?? null) ? $seo : [];
$seo['json_ld'] = $jsonLd;

$meta_description = 'Browse ' . number_format($total ?? 0) . ' premium properties — plots, flats, villas, farmhouses — from APS Dream Home across India.';
$meta_keywords = 'real estate, properties, plots, flats, villas, farmhouses, ' . implode(', ', array_filter([
    $currentFilters['location'] ?? null,
    $currentFilters['type'] ?? null
]));
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.props-filter-glass{background:rgba(255,255,255,0.92);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.6);border-radius:20px;box-shadow:0 8px 32px rgba(0,0,0,0.08);margin-top:-25px;position:relative;z-index:10;padding:0;overflow:hidden}
.props-filter-glass .filter-header{background:linear-gradient(135deg,#f8fafc,#f1f5f9);padding:16px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;cursor:pointer;transition:background 0.2s}
.props-filter-glass .filter-header:hover{background:#f1f5f9}
.props-filter-glass .filter-header h6{margin:0;font-weight:700;color:#1e293b;font-size:0.95rem}
.props-filter-glass .filter-body{padding:20px 24px}
.props-filter-glass .form-label{font-size:0.78rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px}
.props-filter-glass .form-control,.props-filter-glass .form-select{border-radius:10px;border:1.5px solid #e2e8f0;font-size:0.88rem;padding:8px 12px;transition:all 0.2s;background:#fff}
.props-filter-glass .form-control:focus,.props-filter-glass .form-select:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,0.12)}

.props-grid-card{background:#fff;border-radius:16px;overflow:hidden;border:none;box-shadow:0 4px 20px rgba(0,0,0,0.06);transition:all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);position:relative}
.props-grid-card:hover{transform:translateY(-8px);box-shadow:0 12px 40px rgba(0,0,0,0.12)}
.props-grid-card .card-img-wrap{position:relative;overflow:hidden;height:220px}
.props-grid-card .card-img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform 0.6s ease}
.props-grid-card:hover .card-img-wrap img{transform:scale(1.08)}
.props-grid-card .card-img-wrap::after{content:'';position:absolute;bottom:0;left:0;right:0;height:60px;background:linear-gradient(transparent,rgba(0,0,0,0.3))}
.props-grid-card .img-badges{position:absolute;top:12px;left:12px;display:flex;gap:6px;z-index:2}
.props-grid-card .img-badges .badge{padding:5px 12px;border-radius:8px;font-size:0.72rem;font-weight:600;backdrop-filter:blur(8px);letter-spacing:0.3px}
.props-grid-card .img-actions{position:absolute;top:12px;right:12px;display:flex;gap:6px;z-index:2}
.props-grid-card .img-actions .btn{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(8px);background:rgba(255,255,255,0.85);border:none;box-shadow:0 2px 8px rgba(0,0,0,0.1);transition:all 0.2s}
.props-grid-card .img-actions .btn:hover{background:#fff;transform:scale(1.1)}
.props-grid-card .card-body{padding:20px}
.props-grid-card .prop-name{font-size:1.05rem;font-weight:700;color:#1e293b;margin-bottom:4px;line-height:1.3;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden}
.props-grid-card .prop-location{color:#64748b;font-size:0.82rem;margin-bottom:12px;display:flex;align-items:center;gap:4px}
.props-grid-card .prop-location i{color:#0d9488;font-size:0.7rem}
.props-grid-card .prop-features{display:flex;gap:0;margin:12px 0;padding:10px 0;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9}
.props-grid-card .prop-features .feat{flex:1;text-align:center;font-size:0.75rem;color:#64748b}
.props-grid-card .prop-features .feat i{display:block;font-size:0.85rem;color:#0d9488;margin-bottom:3px}
.props-grid-card .prop-features .feat strong{display:block;color:#1e293b;font-size:0.82rem}
.props-grid-card .prop-footer{display:flex;align-items:center;justify-content:space-between;margin-top:4px}
.props-grid-card .prop-price{font-size:1.3rem;font-weight:800;color:#0d9488;line-height:1}
.props-grid-card .prop-price small{font-size:0.7rem;font-weight:500;color:#94a3b8}
.props-grid-card .prop-actions{display:flex;gap:6px}
.props-grid-card .prop-actions .btn{border-radius:10px;font-size:0.78rem;padding:6px 14px;font-weight:600;transition:all 0.2s}
.props-grid-card .btn-interest{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border:none}
.props-grid-card .btn-interest:hover{background:linear-gradient(135deg,#0f766e,#115e59);transform:translateY(-1px)}
.props-grid-card .btn-compare{border:1.5px solid #e2e8f0;color:#64748b;background:transparent}
.props-grid-card .btn-compare:hover{border-color:#0d9488;color:#0d9488;background:rgba(13,148,136,0.05)}
.props-grid-card .btn-compare.added{border-color:#0d9488;color:#fff;background:#0d9488}

.props-pagination{display:flex;justify-content:center;gap:6px;margin-top:32px;margin-bottom:40px}
.props-pagination .page-btn{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;border:1.5px solid #e2e8f0;background:#fff;color:#475569;font-weight:600;font-size:0.88rem;text-decoration:none;transition:all 0.2s}
.props-pagination .page-btn:hover{border-color:#0d9488;color:#0d9488;background:rgba(13,148,136,0.05)}
.props-pagination .page-btn.active{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border-color:transparent;box-shadow:0 4px 12px rgba(13,148,136,0.3)}
.props-pagination .page-btn.disabled{opacity:0.4;pointer-events:none}

.props-empty{text-align:center;padding:60px 20px}
.props-empty .empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
.props-empty .empty-icon i{font-size:2.5rem;color:#0d9488}
.props-empty h5{color:#1e293b;font-weight:700}
.props-empty p{color:#64748b}

@media(max-width:768px){
.props-grid-card .card-img-wrap{height:180px}
.props-grid-card .card-body{padding:16px}
.props-grid-card .prop-price{font-size:1.1rem}
.props-filter-glass .filter-body{padding:16px}
}
</style>

<div class="hero-premium pt-5 pb-5 mb-0" style="background: linear-gradient(135deg, #0f172a, #1e3a5f);">
    <div class="container hero-content premium-reveal fade-up position-relative z-2">
        <h1 class="display-4 fw-bold text-white mb-2"><i class="fas fa-building me-3"></i><?= __('properties') ?></h1>
        <p class="lead text-white-50 mb-4">Discover premium properties across India — plots, flats, villas, farmhouses & more</p>
        <div class="d-flex flex-wrap gap-2">
            <div class="capsule-badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 shadow-sm px-3 py-2">
                <i class="fas fa-check-circle text-success me-1"></i>
                <span><strong id="resultsCount"><?= number_format($total ?? 0) ?></strong> verified properties</span>
            </div>
            <?php if ($hasActiveFilters): ?>
                <div class="capsule-badge bg-success bg-opacity-25 text-white border border-success border-opacity-25 shadow-sm px-3 py-2">
                    <i class="fas fa-filter text-success me-1"></i>
                    <span>Filtered results</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container style-54650">
    <!-- Filters -->
    <div class="props-filter-glass mb-4">
        <div class="filter-header" data-bs-toggle="collapse" data-bs-target="#advancedFilters" role="button">
            <h6><i class="fas fa-sliders-h me-2 style-5793"></i><?= __('advanced_search') ?></h6>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($_SESSION['user_id']) && $hasActiveFilters): ?>
                    <button type="button" class="btn btn-sm style-26680" onclick="event.stopPropagation();triggerSaveSearch()">
                        <i class="fas fa-bookmark me-1"></i>Save
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-secondary style-94626" onclick="event.stopPropagation();resetFilters()" aria-label="Reset filters">
                    <i class="fas fa-redo" aria-hidden="true"></i>
                </button>
                <i class="fas fa-chevron-down style-8890" id="filterChevron"></i>
            </div>
        </div>
        <div class="collapse" id="advancedFilters">
            <div class="filter-body">
                <form method="GET" action="<?php echo BASE_URL; ?>/properties" id="propertyFilterForm" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                    <div class="col-md-4">
                        <label for="q" class="form-label"><i class="fas fa-search me-1"></i><?= __('keyword') ?></label>
                        <input type="text" class="form-control" id="q" name="q" placeholder="Search by name, address..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label"><?= __('filter_type') ?></label>
                        <select class="form-select" id="type" name="type">
                            <option value=""><?= __('all') ?></option>
                            <?php foreach (['plot','house','flat','shop','farmhouse','villa','land'] as $t): ?>
                                <option value="<?= e($t ?? '') ?>" <?= ($_GET['type'] ?? '') === $t ? 'selected' : ''; ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="listing" class="form-label"><?= __('filter_listing') ?></label>
                        <select class="form-select" id="listing" name="listing">
                            <option value=""><?= __('buy_and_rent') ?></option>
                            <option value="sell" <?= ($_GET['listing'] ?? '') === 'sell' ? 'selected' : ''; ?>><?= __('for_sale') ?></option>
                            <option value="rent" <?= ($_GET['listing'] ?? '') === 'rent' ? 'selected' : ''; ?>><?= __('for_rent') ?></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="location" class="form-label"><?= __('filter_location') ?></label>
                        <select class="form-select" id="location" name="location">
                            <option value=""><?= __('all') ?></option>
                            <?php foreach (($locations ?? []) as $loc): ?>
                                <option value="<?= htmlspecialchars($loc ?? '') ?>" <?= ($_GET['location'] ?? '') === $loc ? 'selected' : ''; ?>><?= htmlspecialchars($loc ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sort" class="form-label"><?= __('sort_by') ?></label>
                        <select class="form-select" id="sort" name="sort" onchange="document.getElementById('propertyFilterForm').submit()">
                            <option value="newest" <?= ($_GET['sort'] ?? 'newest') === 'newest' ? 'selected' : ''; ?>><?= __('newest_first') ?></option>
                            <option value="oldest" <?= ($_GET['sort'] ?? '') === 'oldest' ? 'selected' : ''; ?>><?= __('oldest_first') ?></option>
                            <option value="price_low" <?= ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : ''; ?>><?= __('price_low_high') ?></option>
                            <option value="price_high" <?= ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : ''; ?>><?= __('price_high_low') ?></option>
                            <option value="area_large" <?= ($_GET['sort'] ?? '') === 'area_large' ? 'selected' : ''; ?>><?= __('area_large_first') ?></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="bedrooms" class="form-label"><?= __('bedrooms_min') ?></label>
                        <select class="form-select" id="bedrooms" name="bedrooms">
                            <option value=""><?= __('any') ?></option>
                            <?php foreach ([1,2,3,4,5] as $b): ?>
                                <option value="<?= $b ?>" <?= ($_GET['bedrooms'] ?? '') == $b ? 'selected' : ''; ?>><?= $b ?>+ <?= __('bhk') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="bathrooms" class="form-label"><?= __('bathrooms_min') ?></label>
                        <select class="form-select" id="bathrooms" name="bathrooms">
                            <option value=""><?= __('any') ?></option>
                            <?php foreach ([1,2,3,4] as $b): ?>
                                <option value="<?= $b ?>" <?= ($_GET['bathrooms'] ?? '') == $b ? 'selected' : ''; ?>><?= $b ?>+</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="furnished" class="form-label"><?= __('furnished') ?></label>
                        <select class="form-select" id="furnished" name="furnished">
                            <option value=""><?= __('any') ?></option>
                            <option value="unfurnished" <?= ($_GET['furnished'] ?? '') === 'unfurnished' ? 'selected' : ''; ?>><?= __('unfurnished') ?></option>
                            <option value="semi-furnished" <?= ($_GET['furnished'] ?? '') === 'semi-furnished' ? 'selected' : ''; ?>><?= __('semi_furnished') ?></option>
                            <option value="fully-furnished" <?= ($_GET['furnished'] ?? '') === 'fully-furnished' ? 'selected' : ''; ?>><?= __('fully_furnished') ?></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="min_price" class="form-label"><?= __('min_price') ?></label>
                        <select class="form-select" id="min_price" name="min_price">
                            <option value=""><?= __('no_min') ?></option>
                            <?php foreach ([100000=>'1L',500000=>'5L',1000000=>'10L',2000000=>'20L',5000000=>'50L',10000000=>'1Cr'] as $val=>$lbl): ?>
                                <option value="<?= $val ?>" <?= ($_GET['min_price'] ?? '') == $val ? 'selected' : ''; ?>>&#8377;<?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="max_price" class="form-label"><?= __('max_price') ?></label>
                        <select class="form-select" id="max_price" name="max_price">
                            <option value=""><?= __('no_max') ?></option>
                            <?php foreach ([500000=>'5L',1000000=>'10L',2000000=>'20L',5000000=>'50L',10000000=>'1Cr',20000000=>'2Cr',50000000=>'5Cr'] as $val=>$lbl): ?>
                                <option value="<?= $val ?>" <?= ($_GET['max_price'] ?? '') == $val ? 'selected' : ''; ?>>&#8377;<?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="area_min" class="form-label"><?= __('min_area') ?> (sqft)</label>
                        <input type="number" class="form-control" id="area_min" name="area_min" placeholder="Min" min="0" value="<?= htmlspecialchars($_GET['area_min'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="area_max" class="form-label"><?= __('max_area') ?> (sqft)</label>
                        <input type="number" class="form-control" id="area_max" name="area_max" placeholder="Max" min="0" value="<?= htmlspecialchars($_GET['area_max'] ?? '') ?>">
                    </div>
                    <div class="col-12 d-flex gap-2 align-items-center pt-2 flex-wrap">
                        <button type="submit" class="btn px-4 style-55218">
                            <i class="fas fa-search me-1"></i><?= __('search') ?>
                        </button>
                        <button type="button" class="btn btn-teal px-4" id="aiSearchBtn" onclick="openAISearchModal()">
                            <i class="fas fa-robot me-1"></i><?= __('ai_search') ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-secondary style-46740">
                            <i class="fas fa-times me-1"></i><?= __('clear_all') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cross-link Section: Also Explore -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center style-1563">
                <span class="fw-semibold text-success me-2"><i class="fas fa-compass me-1"></i><?= __('also_explore') ?></span>
                <a href="<?= BASE_URL ?>/plots" class="btn btn-sm px-3 style-6722">
                    <i class="fas fa-vector-square me-1"></i>Plots
                </a>
                <a href="<?= BASE_URL ?>/projects" class="btn btn-sm px-3 style-97522">
                    <i class="fas fa-project-diagram me-1"></i>Projects
                </a>
                <a href="<?= BASE_URL ?>/colonies" class="btn btn-sm px-3 style-66828">
                    <i class="fas fa-city me-1"></i><?= __('colonies') ?>
                </a>
            </div>
        </div>
    </div>

    <!-- View Toggle Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted style-49273">
            Showing <strong><?= count($properties ?? []) ?></strong> of <strong><?= number_format($total ?? 0) ?></strong> properties
        </span>
        <div class="d-flex gap-2">
            <div class="btn-group btn-group-sm" role="group" aria-label="View mode">
                <button type="button" class="btn btn-outline-secondary active" id="gridViewBtn" onclick="setView('grid')" aria-label="Grid view" aria-pressed="true"><i class="fas fa-th-large" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-outline-secondary" id="mapViewBtn" onclick="setView('map')" disabled aria-label="Map view" title="Coming soon"><i class="fas fa-map-marked-alt" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="row" id="propertiesContainer" data-experiment="property_card_layout" data-variant="<?= htmlspecialchars($_SESSION['experiments']['property_card_layout'] ?? 'current', ENT_QUOTES) ?>">
        <?php
            $cardLayout = $_SESSION['experiments']['property_card_layout'] ?? 'current';
            $cardColClass = $cardLayout === 'compact' ? 'col-lg-3 col-md-6' : 'col-lg-4 col-md-6';
        ?>
        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $idx => $property): ?>
                <div class="<?= htmlspecialchars($cardColClass ?? '') ?> mb-4 scroll-reveal" class="style-86452">
                    <div class="card props-grid-card glass-card h-100" data-property-id="<?= (int)($property['id'] ?? 0) ?>">
                        <div class="card-img-wrap">
                            <?php
                                $imgSrc = BASE_URL . '/assets/images/properties/' . htmlspecialchars($property['image'] ?? '');
                                if (empty($property['image'])) $imgSrc = BASE_URL . '/assets/images/placeholder/property.svg';
                                $propTitle = $property['title'] ?? $property['name'] ?? 'Property';
                                $propType = $property['type'] ?? $property['property_type'] ?? '';
                                $propLocation = $property['location'] ?? $property['address'] ?? '';
                                $propListingType = $property['listing_type'] ?? 'sell';
                            ?>
                            <img loading="lazy" src="<?= $imgSrc ?>"
                                 alt="<?= htmlspecialchars($propTitle ?? '') ?>"
                                 onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">

                            <div class="img-badges">
                                <span class="badge style-55032">
                                    <?= $propListingType === 'rent' ? 'FOR RENT' : 'FOR SALE' ?>
                                </span>
                                <?php if (!empty($propType)): ?>
                                    <span class="badge style-11190">
                                        <?= strtoupper($propType) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="img-actions">
                                <button class="btn favorite-btn" data-id="<?= $property['id'] ?? '' ?>" title="<?= __('add_to_favorites') ?>" aria-label="<?= __('add_to_favorites') ?>" onclick="toggleFavorite(this)">
                                    <i class="far fa-heart style-53984" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="prop-name"><?= htmlspecialchars($propTitle ?? '') ?></h5>
                            <div class="prop-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($propLocation ?? '') ?>
                            </div>

                            <?php if (!empty($property['description'])): ?>
                                <p class="style-88890">
                                    <?= htmlspecialchars($property['description'] ?? '') ?>
                                </p>
                            <?php endif; ?>

                            <div class="prop-features">
                                <?php if (!empty($property['area_sqft'])): ?>
                                <div class="feat">
                                    <i class="fas fa-vector-square"></i>
                                    <strong><?= number_format((float)$property['area_sqft']) ?></strong>
                                    <?= __('sq_ft') ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($property['bedrooms'])): ?>
                                <div class="feat">
                                    <i class="fas fa-bed"></i>
                                    <strong><?= (int)$property['bedrooms'] ?></strong>
                                    <?= __('bhk') ?>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($property['bathrooms'])): ?>
                                <div class="feat">
                                    <i class="fas fa-bath"></i>
                                    <strong><?= (int)$property['bathrooms'] ?></strong>
                                    Bath
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($property['furnished'])): ?>
                                <div class="feat">
                                    <i class="fas fa-couch"></i>
                                    <strong><?= ucfirst($property['furnished']) ?></strong>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="prop-footer">
                                <div class="prop-price">
                                    &#8377;<?= number_format((float)($property['price'] ?? 0)) ?>
                                    <?php if (($property['listing_type'] ?? 'sell') === 'rent'): ?>
                                        <small>/month</small>
                                    <?php endif; ?>
                                </div>
                                <div class="prop-actions">
                                    <button class="btn btn-interest"
                                            data-id="<?= $property['id'] ?? '' ?>"
                                            data-name="<?= htmlspecialchars($propTitle ?? '') ?>"
                                            onclick="showPropertyInterestModal(this)">
                                        <i class="fas fa-hand-pointer me-1"></i>Interested
                                    </button>
                                    <button class="btn btn-compare add-to-compare" data-id="<?= $property['id'] ?? '' ?>" onclick="addToCompare(this)" title="<?= __('add_to_compare') ?>" aria-label="<?= __('add_to_compare') ?>">
                                        <i class="fas fa-balance-scale" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="props-empty scroll-reveal">
                    <div class="empty-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h5><?= __('no_properties') ?></h5>
                    <p class="mb-3"><?= __('no_results_tip') ?></p>
                    <a href="<?= BASE_URL ?>/properties" class="btn px-4 style-11181">
                        <?= __('view_all') ?> Properties
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 0) > 1): ?>
        <?php
        $paginationParams = $_GET;
        unset($paginationParams['page']);
        ?>
        <div class="props-pagination scroll-reveal">
            <?php if (($page ?? 1) > 1): ?>
                <a class="page-btn" href="?<?= http_build_query(array_merge($paginationParams, ['page' => ($page - 1)])) ?>">
                    <i class="fas fa-chevron-left style-5315"></i>
                </a>
            <?php endif; ?>
            <?php
            $startPage = max(1, ($page ?? 1) - 2);
            $endPage = min($totalPages, ($page ?? 1) + 2);
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a class="page-btn <?= $i === ($page ?? 1) ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($paginationParams, ['page' => $i])) ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if (($page ?? 1) < $totalPages): ?>
                <a class="page-btn" href="?<?= http_build_query(array_merge($paginationParams, ['page' => ($page + 1)])) ?>">
                    <i class="fas fa-chevron-right style-5315"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Saved Searches -->
<?php if (!empty($_SESSION['user_id'])): ?>
    <?php $savedSearches = $savedSearches ?? []; ?>
    <?php $isLoggedIn = true; ?>
    <?php include __DIR__ . '/../components/saved_search_dropdown.php'; ?>
<?php endif; ?>
<?php include __DIR__ . '/../components/save_search_modal.php'; ?>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('advancedFilters')?.addEventListener('show.bs.collapse', () => {
    document.getElementById('filterChevron').style.transform = 'rotate(180deg)';
});
document.getElementById('advancedFilters')?.addEventListener('hide.bs.collapse', () => {
    document.getElementById('filterChevron').style.transform = 'rotate(0)';
});

const I18N = <?= json_encode(['added' => __('added'), 'failed_to_add' => __('failed_to_add'), 'network_error' => __('network_error'), 'map_coming_soon' => __('map_coming_soon'), 'add_to_favorites' => __('add_to_favorites'), 'remove_from_favorites' => __('remove_from_favorites')]) ?>;

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
            if (isFav) { icon.className = 'far fa-heart'; btn.title = I18N.add_to_favorites; }
            else { icon.className = 'fas fa-heart'; btn.title = I18N.remove_from_favorites; }
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
    fetch(BASE_URL + '/property-comparison/add', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }})
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.add('added');
            updateCompareBadge(d.count);
        } else { if (window.APS?.toast) window.APS.toast(d.error || I18N.failed_to_add, 'error'); else alert(d.error || I18N.failed_to_add); }
    }).catch(() => { if (window.APS?.toast) window.APS.toast(I18N.network_error, 'error'); else alert(I18N.network_error); });
}

function updateCompareBadge(count) {
    const badge = document.getElementById('compareBadge');
    if (badge) {
        if (count === undefined) {
            fetch(BASE_URL + '/property-comparison', { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                .then(() => { let s = parseInt(localStorage.getItem('property_compare_count') || '0'); badge.textContent = s; badge.style.display = s > 0 ? 'inline' : 'none'; })
                .catch(() => { badge.textContent = 0; badge.style.display = 'none'; });
        } else {
            localStorage.setItem('property_compare_count', count);
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }
    }
}

function setView(view) {
    if (view === 'map') { showToast(I18N.map_coming_soon, 'info'); return; }
    document.getElementById('gridViewBtn').classList.add('active');
    document.getElementById('mapViewBtn').classList.remove('active');
}

function resetFilters() { window.location.href = BASE_URL + '/properties'; }

document.addEventListener('DOMContentLoaded', function () { updateCompareBadge(); });

function showPropertyInterestModal(btn) {
    document.getElementById('propInterestId').value = btn.dataset.id;
    document.getElementById('propInterestName').textContent = btn.dataset.name;
    document.getElementById('propInterestForm').style.display = 'block';
    document.getElementById('propInterestSuccess').style.display = 'none';
    new bootstrap.Modal(document.getElementById('propertyInterestModal')).show();
}
function selectPropBudget(el) {
    document.querySelectorAll('.prop-budget-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('propInterestBudget').value = el.textContent;
}
function submitPropertyInterest(e) {
    e.preventDefault();
    const form = document.getElementById('propInterestForm');
    const btn = document.getElementById('propInterestSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
    const fd = new FormData(form);
    fetch(BASE_URL + '/property/interest', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                form.style.display = 'none';
                document.getElementById('propInterestSuccess').style.display = 'block';
                setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('propertyInterestModal')).hide(), 2500);
            } else { if (window.APS?.toast) window.APS.toast(data.message || 'Something went wrong.', 'error'); else alert(data.message || 'Something went wrong.'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Interest'; }
        })
        .catch(() => { if (window.APS?.toast) window.APS.toast('Network error.', 'error'); else alert('Network error.'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Interest'; });
}
</script>

<div class="modal fade" id="propertyInterestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered style-77674">
        <div class="modal-content style-51436">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h6 class="fw-bold mb-0 style-8420">I'm Interested</h6>
                    <small class="text-muted" id="propInterestName"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="propInterestForm" onsubmit="submitPropertyInterest(event)">
    <?php echo CSRFProtection::csrfField(); ?>
                    <input type="hidden" name="property_id" id="propInterestId">
                    <input type="hidden" name="source" value="property_listing">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone Number *</label>
                        <input type="tel" name="phone" class="form-control style-46740" placeholder="+91 98765 43210" required value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Your Name</label>
                        <input type="text" name="name" class="form-control style-46740" placeholder="Enter your name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Budget Range</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach (['Under 10L','10L-25L','25L-50L','50L-1Cr','1Cr+'] as $budget): ?>
                            <button type="button" class="btn btn-sm prop-budget-chip style-35248" onclick="selectPropBudget(this)"><?= $budget ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="budget" id="propInterestBudget">
                    </div>
                    <button type="submit" class="btn w-100" id="propInterestSubmitBtn" class="style-55218">
                        <i class="fas fa-paper-plane me-1"></i>Submit Interest
                    </button>
                </form>
                <div id="propInterestSuccess" class="text-center py-3 style-2248">
                    <i class="fas fa-check-circle fa-3x mb-3 style-2154"></i>
                    <h6 class="fw-bold">Interest Recorded!</h6>
                    <p class="text-muted small mb-0">Our team will contact you shortly.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">.prop-budget-chip.active{background:#0d9488!important;color:#fff!important;border-color:#0d9488!important}</style>

<!-- AI Search Modal -->
<div class="modal fade" id="aiSearchModal" tabindex="-1" aria-labelledby="aiSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glassmorphism">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-teal" id="aiSearchModalLabel">
                    <i class="fas fa-robot me-2"></i><?= __('ai_search_title') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4"><?= __('ai_search_description') ?></p>
                <form id="aiSearchForm">
                    <div class="mb-3">
                        <label class="form-label fw-medium"><?= __('describe_your_dream_home') ?></label>
                        <textarea class="form-control" name="query" rows="4" placeholder="<?= __('ai_search_placeholder') ?>" required></textarea>
                        <div class="form-text"><?= __('ai_search_help') ?></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?= __('budget_range') ?></label>
                            <select class="form-select" name="budget_range">
                                <option value=""><?= __('any_budget') ?></option>
                                <option value="0-500000"><?= __('under_5l') ?></option>
                                <option value="500000-1000000"><?= __('5l_10l') ?></option>
                                <option value="1000000-2000000"><?= __('10l_20l') ?></option>
                                <option value="2000000-5000000"><?= __('20l_50l') ?></option>
                                <option value="5000000-10000000"><?= __('50l_1cr') ?></option>
                                <option value="10000000-"><?= __('above_1cr') ?></option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= __('property_type') ?></label>
                            <select class="form-select" name="property_type">
                                <option value=""><?= __('any_type') ?></option>
                                <option value="plot">Plot</option>
                                <option value="flat">Flat/Apartment</option>
                                <option value="villa">Villa</option>
                                <option value="farmhouse">Farmhouse</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-secondary flex-grow-1" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> <?= __('cancel') ?>
                        </button>
                        <button type="submit" class="btn btn-teal flex-grow-1" id="aiSearchSubmitBtn">
                            <i class="fas fa-robot me-1"></i> <span id="aiSearchBtnText"><?= __('find_matches') ?></i>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="aiSearchSpinner" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
                <div id="aiSearchResults" class="mt-4 d-none">
                    <h6 class="fw-bold text-teal mb-3"><i class="fas fa-robot me-1"></i> <?= __('ai_matches_found') ?></h6>
                    <div id="aiResultsContainer" class="row g-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Smart Registration Behavior Tracking -->
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var token = (document.cookie.match('(^|;)\\s*smart_reg_token\\s*=\\s*([^;]+)') || [])[2];
    if (!token) return;
    function track(type, data) {
        try {
            var x = new XMLHttpRequest();
            x.open('POST', '<?= BASE_URL ?>/api/smart-register/track', true);
            x.setRequestHeader('Content-Type', 'application/json');
            x.send(JSON.stringify({ token: token, event_type: type, event_data: data || null, page_url: window.location.href }));
        } catch (e) { console.error("Error:", e); }
    }
    // Track search events
    var searchForm = document.querySelector('form[action*="properties"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            track('search', { query: (searchForm.querySelector('[name="q"]') || {}).value || '' });
        });
    }
    // Track property card clicks
    document.querySelectorAll('.property-card a, [data-property-id]').forEach(function(el) {
        el.addEventListener('click', function() {
            var id = el.getAttribute('data-property-id') || '';
            track('page_view', { action: 'property_card_click', property_id: id });
        });
    });
    
    // AI Search Modal
    var aiSearchModal = new bootstrap.Modal(document.getElementById('aiSearchModal'));
    var aiSearchForm = document.getElementById('aiSearchForm');
    var aiSearchBtn = document.getElementById('aiSearchSubmitBtn');
    var aiSearchBtnText = document.getElementById('aiSearchBtnText');
    var aiSearchSpinner = document.getElementById('aiSearchSpinner');
    var aiSearchResults = document.getElementById('aiSearchResults');
    var aiResultsContainer = document.getElementById('aiResultsContainer');
    
    if (aiSearchForm) {
        aiSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(aiSearchForm);
            var btn = aiSearchBtn;
            var btnText = aiSearchBtnText;
            var spinner = aiSearchSpinner;
            
            // Show loading state
            btn.disabled = true;
            btnText.textContent = '<?= __("searching") ?>';
            spinner.classList.remove('d-none');
            
            fetch('<?= BASE_URL ?>/api/properties/ai-search', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                // Reset button
                btn.disabled = false;
                btnText.textContent = '<?= __("find_matches") ?>';
                spinner.classList.add('d-none');
                
                if (data.success && data.properties.length > 0) {
                    // Show results
                    aiResultsContainer.innerHTML = '';
                    data.properties.forEach(function(prop) {
                        var image = prop.image ? '<?= BASE_URL ?>/' + prop.image : '<?= BASE_URL ?>/assets/images/placeholder/property.svg';
                        var card = document.createElement('div');
                        card.className = 'col-md-6 col-lg-4';
                        card.innerHTML = 
                            '<div class="card property-card h-100 glassmorphism">' +
                            '  <div class="card-img-wrap">' +
                            '    <img src="' + image + '" class="card-img-top" alt="' + prop.title + '" loading="lazy">' +
                            '    <div class="img-badges">' +
                            '      <span class="badge bg-primary">' + prop.type + '</span>' +
                            '      <span class="badge bg-warning text-dark">AI Match: ' + (prop.ai_score || 0) + '%</span>' +
                            '    </div>' +
                            '  </div>' +
                            '  <div class="card-body">' +
                            '    <h6 class="prop-name">' + prop.title + '</h6>' +
                            '    <div class="prop-location"><i class="fas fa-map-marker-alt"></i> <span class="text-muted">' + prop.location + '</span></div>' +
                            '    <div class="prop-features">' +
                            '      <div class="feat"><i class="fas fa-ruler-combined"></i><strong>' + prop.area + '</strong> sqft</div>' +
                            '      <div class="feat"><i class="fas fa-bed"></i><strong>' + (prop.bedrooms || 0) + '</strong> BHK</div>' +
                            '      <div class="feat"><i class="fas fa-bath"></i><strong>' + (prop.bathrooms || 0) + '</strong></div>' +
                            '    </div>' +
                            '    <div class="prop-footer">' +
                            '      <span class="prop-price">&#8377;' + prop.price_formatted + '</span>' +
                            '      <a href="' + BASE_URL + '/property/' + prop.id + '" class="btn btn-teal btn-sm">' + 'View Details' + '</a>' +
                            '    </div>' +
                            '  </div>' +
                            '</div>';
                        aiResultsContainer.appendChild(card);
                    });
                    aiSearchResults.classList.remove('d-none');
                    // Scroll to results
                    aiSearchResults.scrollIntoView({ behavior: 'smooth' });
                } else if (data.success && data.properties.length === 0) {
                    aiResultsContainer.innerHTML = '<div class="col-12 text-center py-4"><i class="fas fa-search fa-2x text-muted mb-2"></i><p class="text-muted">' + '<?= __("no_matches_found") ?>' + '</p></div>';
                    aiSearchResults.classList.remove('d-none');
                } else {
                    alert(data.message || '<?= __("search_failed") ?>');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btnText.textContent = '<?= __("find_matches") ?>';
                spinner.classList.add('d-none');
                alert('<?= __("search_error") ?>');
            });
        });
    }
    
    // Track search events
    var searchForm = document.querySelector('form[action*="properties"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            track('search', { query: (searchForm.querySelector('[name="q"]') || {}).value || '' });
        });
    }
    // Track property card clicks
    document.querySelectorAll('.property-card a, [data-property-id]').forEach(function(el) {
        el.addEventListener('click', function() {
            var id = el.getAttribute('data-property-id') || '';
            track('page_view', { action: 'property_card_click', property_id: id });
        });
    });
})();
</script>
