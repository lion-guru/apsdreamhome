<?php
$page_title = 'Properties for Rent - APS Dream Home';
$current_page = 'rent';
$properties = $properties ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$stats = $stats ?? ['total' => 0, 'min_price' => 0, 'max_price' => 0, 'avg_price' => 0];
$filters = $filters ?? [];
if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }
$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')) ?: '919277121112';
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.rent-hero{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);position:relative;overflow:hidden}
.rent-hero::before{content:'';position:absolute;top:-50%;right:-20%;width:600px;height:600px;background:radial-gradient(circle,rgba(255,255,255,0.08) 0%,transparent 70%);border-radius:50%}
.rent-hero::after{content:'';position:absolute;bottom:-30%;left:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(255,255,255,0.05) 0%,transparent 70%);border-radius:50%}
.rent-stat-card{background:rgba(255,255,255,0.12);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.2);border-radius:14px;padding:16px 20px;text-align:center;transition:all 0.3s}
.rent-stat-card:hover{background:rgba(255,255,255,0.18);transform:translateY(-3px)}
.rent-stat-card .stat-value{font-size:1.5rem;font-weight:800;color:#fff;line-height:1.1}
.rent-stat-card .stat-label{font-size:0.75rem;color:rgba(255,255,255,0.7);margin-top:4px;text-transform:uppercase;letter-spacing:0.5px}

.rent-filter-glass{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.6);border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.08);margin-top:-30px;position:relative;z-index:10;padding:20px 24px}
.rent-filter-glass .form-label{font-size:0.78rem;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px}
.rent-filter-glass .form-control,.rent-filter-glass .form-select{border-radius:10px;border:1.5px solid #e2e8f0;font-size:0.88rem;padding:8px 12px;transition:all 0.2s;background:#fff}
.rent-filter-glass .form-control:focus,.rent-filter-glass .form-select:focus{border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,0.12)}

.rent-card{background:#fff;border-radius:16px;overflow:hidden;border:none;box-shadow:0 4px 20px rgba(0,0,0,0.06);transition:all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);position:relative}
.rent-card:hover{transform:translateY(-8px);box-shadow:0 12px 40px rgba(0,0,0,0.12)}
.rent-card .card-img-wrap{position:relative;overflow:hidden;height:220px}
.rent-card .card-img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform 0.6s ease}
.rent-card:hover .card-img-wrap img{transform:scale(1.08)}
.rent-card .card-img-wrap::after{content:'';position:absolute;bottom:0;left:0;right:0;height:60px;background:linear-gradient(transparent,rgba(0,0,0,0.3))}
.rent-card .img-badges{position:absolute;top:12px;left:12px;display:flex;gap:6px;z-index:2}
.rent-card .badge{padding:5px 12px;border-radius:8px;font-size:0.72rem;font-weight:600;backdrop-filter:blur(8px);letter-spacing:0.3px}
.rent-card .card-body{padding:20px}
.rent-card .prop-name{font-size:1.05rem;font-weight:700;color:#1e293b;margin-bottom:4px;line-height:1.3;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden}
.rent-card .prop-location{color:#64748b;font-size:0.82rem;margin-bottom:12px;display:flex;align-items:center;gap:4px}
.rent-card .prop-location i{color:#667eea;font-size:0.7rem}
.rent-card .prop-features{display:flex;gap:0;margin:12px 0;padding:10px 0;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9}
.rent-card .prop-features .feat{flex:1;text-align:center;font-size:0.75rem;color:#64748b}
.rent-card .prop-features .feat i{display:block;font-size:0.85rem;color:#667eea;margin-bottom:3px}
.rent-card .prop-features .feat strong{display:block;color:#1e293b;font-size:0.82rem}
.rent-card .prop-footer{display:flex;align-items:center;justify-content:space-between;margin-top:4px}
.rent-card .prop-price{font-size:1.3rem;font-weight:800;color:#667eea;line-height:1}
.rent-card .prop-price small{font-size:0.7rem;font-weight:500;color:#94a3b8}
.rent-card .prop-actions .btn{border-radius:10px;font-size:0.78rem;padding:6px 14px;font-weight:600;transition:all 0.2s}
.rent-card .btn-interest{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none}
.rent-card .btn-interest:hover{background:linear-gradient(135deg,#764ba2,#667eea);transform:translateY(-1px)}

.rent-pagination{display:flex;justify-content:center;gap:6px;margin-top:32px;margin-bottom:40px}
.rent-pagination .page-btn{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;border:1.5px solid #e2e8f0;background:#fff;color:#475569;font-weight:600;font-size:0.88rem;text-decoration:none;transition:all 0.2s}
.rent-pagination .page-btn:hover{border-color:#667eea;color:#667eea;background:rgba(102,126,234,0.05)}
.rent-pagination .page-btn.active{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-color:transparent;box-shadow:0 4px 12px rgba(102,126,234,0.3)}

.rent-empty{text-align:center;padding:60px 20px}
.rent-empty .empty-icon{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#f0f0ff,#e8e0ff);display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
.rent-empty .empty-icon i{font-size:2.5rem;color:#667eea}

@media(max-width:768px){
.rent-card .card-img-wrap{height:180px}
.rent-card .card-body{padding:16px}
.rent-card .prop-price{font-size:1.1rem}
.rent-filter-glass{padding:16px}
.rent-stat-card .stat-value{font-size:1.2rem}
}
</style>

<!-- Hero -->
<section class="rent-hero py-5 text-white position-relative" class="style-45611">
    <div class="container text-center py-4 position-relative" class="style-9174">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-key me-3"></i>Properties for Rent</h1>
        <p class="lead mb-4" class="style-49619">Find your perfect rental home, flat, or commercial space across India</p>
        <div class="row g-3 justify-content-center" class="style-23275">
            <div class="col-6 col-md-3">
                <div class="rent-stat-card">
                    <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                    <div class="stat-label">Available</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="rent-stat-card">
                    <div class="stat-value">&#8377;<?= number_format(($stats['min_price'] ?? 0) / 1000) ?>K</div>
                    <div class="stat-label">Starting</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="rent-stat-card">
                    <div class="stat-value">&#8377;<?= number_format(($stats['avg_price'] ?? 0) / 1000) ?>K</div>
                    <div class="stat-label">Avg Rent</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="rent-stat-card">
                    <div class="stat-value">Verified</div>
                    <div class="stat-label">Listings</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filters -->
<div class="container" class="style-54650">
    <div class="rent-filter-glass mb-4">
        <form method="GET" action="<?= BASE_URL ?>/rent" class="row g-3 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
            <div class="col-md-3">
                <label class="form-label"><i class="fas fa-search me-1"></i>Search</label>
                <input type="text" class="form-control" name="q" placeholder="Name, address..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" placeholder="City area..." value="<?= htmlspecialchars($filters['location'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select class="form-select" name="type">
                    <option value="">All Types</option>
                    <?php foreach (['flat','house','villa','apartment','shop','farmhouse','plot','land'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($filters['type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Bedrooms</label>
                <select class="form-select" name="bedrooms">
                    <option value="0">Any</option>
                    <?php for ($b = 1; $b <= 5; $b++): ?>
                        <option value="<?= $b ?>" <?= (int)($filters['bedrooms'] ?? 0) === $b ? 'selected' : '' ?>><?= $b ?> BHK+</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Min &#8377;</label>
                <input type="number" class="form-control" name="min_price" placeholder="0" value="<?= (int)($filters['min_price'] ?? 0) ?: '' ?>">
            </div>
            <div class="col-md-1">
                <label class="form-label">Max &#8377;</label>
                <input type="number" class="form-control" name="max_price" placeholder="50000" value="<?= (int)($filters['max_price'] ?? 0) ?: '' ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn w-100" class="style-96284">
                    <i class="fas fa-search me-1"></i>Search
                </button>
            </div>
        </form>
    </div>

    <!-- Results Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" class="style-8420">
            <i class="fas fa-home me-2" class="style-23141"></i>
            <?= number_format($total) ?> Rental <?= $total === 1 ? 'Property' : 'Properties' ?> Found
        </h5>
        <a href="<?= BASE_URL ?>/list-property" class="btn btn-sm" class="style-18366">
            <i class="fas fa-plus me-1"></i>List Your Property
        </a>
    </div>

    <!-- Property Grid -->
    <div class="row g-4 mb-4">
        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $property): ?>
                <?php
                $propTitle = $property['name'] ?? 'Rental Property';
                $propImage = !empty($property['image'])
                    ? BASE_URL . '/assets/images/properties/' . $property['image']
                    : BASE_URL . '/assets/images/properties/placeholder.jpg';
                $propPrice = (float)($property['price'] ?? 0);
                ?>
                <div class="col-md-6 col-lg-4 scroll-reveal">
                    <div class="rent-card h-100">
                        <div class="card-img-wrap">
                            <img src="<?= $propImage ?>" alt="<?= htmlspecialchars($propTitle) ?>" loading="lazy" onerror="this.src='<?= BASE_URL ?>/assets/images/properties/placeholder.jpg'">
                            <div class="img-badges">
                                <span class="badge" class="style-90511">
                                    <i class="fas fa-key me-1"></i>For Rent
                                </span>
                                <?php if (!empty($property['is_featured'])): ?>
                                    <span class="badge" class="style-7462">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="prop-name"><?= htmlspecialchars($propTitle) ?></div>
                            <div class="prop-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($property['location'] ?? $property['city_name'] ?? $property['address'] ?? 'Location TBD') ?>
                            </div>

                            <?php if (!empty($property['description'])): ?>
                                <p class="style-88890">
                                    <?= htmlspecialchars($property['description']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="prop-features">
                                <?php if (!empty($property['area_sqft'])): ?>
                                <div class="feat">
                                    <i class="fas fa-vector-square"></i>
                                    <strong><?= number_format((float)$property['area_sqft']) ?></strong>
                                    sqft
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($property['bedrooms'])): ?>
                                <div class="feat">
                                    <i class="fas fa-bed"></i>
                                    <strong><?= (int)$property['bedrooms'] ?></strong>
                                    BHK
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

                            <div class="prop-footer mt-auto">
                                <div class="prop-price">
                                    &#8377;<?= number_format($propPrice) ?>
                                    <small>/month</small>
                                </div>
                                <div class="prop-actions">
                                    <button class="btn btn-interest"
                                            data-id="<?= $property['id'] ?? '' ?>"
                                            data-name="<?= htmlspecialchars($propTitle) ?>"
                                            onclick="showPropertyInterestModal(this)">
                                        <i class="fas fa-hand-pointer me-1"></i>Interested
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="rent-empty">
                    <div class="empty-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h5>No rental properties found</h5>
                    <p class="text-muted mb-3">Try adjusting your filters or check back later for new listings.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="<?= BASE_URL ?>/rent" class="btn px-4" class="style-18366">
                            Clear Filters
                        </a>
                        <a href="tel:<?= $phoneRaw ?>" class="btn btn-outline-primary px-4" class="style-46740">
                            <i class="fas fa-phone me-2"></i>Call Us
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <?php $paginationParams = $_GET; unset($paginationParams['page']); ?>
        <div class="rent-pagination">
            <?php if ($page > 1): ?>
                <a class="page-btn" href="?<?= http_build_query(array_merge($paginationParams, ['page' => $page - 1])) ?>">
                    <i class="fas fa-chevron-left" class="style-5315"></i>
                </a>
            <?php endif; ?>
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a class="page-btn <?= $i === $page ? 'active' : '' ?>" href="?<?= http_build_query(array_merge($paginationParams, ['page' => $i])) ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a class="page-btn" href="?<?= http_build_query(array_merge($paginationParams, ['page' => $page + 1])) ?>">
                    <i class="fas fa-chevron-right" class="style-5315"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- CTA Section -->
<section class="py-5 mt-4" class="style-83847">
    <div class="container text-center">
        <h3 class="fw-bold mb-3" class="style-8420">Have a Property to Rent Out?</h3>
        <p class="text-muted mb-4">List your property for free and reach thousands of potential tenants</p>
        <a href="<?= BASE_URL ?>/list-property" class="btn btn-lg px-5" class="style-32749">
            <i class="fas fa-plus-circle me-2"></i>List Property for Free
        </a>
    </div>
</section>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function showPropertyInterestModal(btn) {
    const id = btn.dataset.id;
    const name = btn.dataset.name;
    if (!id) return;
    const phone = '<?= $phoneRaw ?>';
    window.open('https://wa.me/<?= $phoneRaw ?>?text=' + encodeURIComponent('Hi, I am interested in renting: ' + name + ' (ID: ' + id + ')'), '_blank');
}
</script>
