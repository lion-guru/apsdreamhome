<!-- Page Header -->
<section class="resell-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Resell Properties</h1>
        <p class="lead mb-0">Buy directly from individual sellers. No middleman, genuine deals.</p>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $crumb['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<!-- Filters Section -->
<section class="py-4 bg-white border-bottom shadow-sm sticky-top" style="top: 70px; z-index: 1000;">
    <div class="container">
        <form action="" method="GET" class="row g-3">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Search title, address..." value="<?= h($_GET['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select name="city" class="form-select bg-light">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= $city ?>" <?= ($_GET['city'] ?? '') == $city ? 'selected' : '' ?>><?= $city ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select bg-light">
                    <option value="">Property Type</option>
                    <?php foreach ($property_types as $type): ?>
                        <option value="<?= $type ?>" <?= ($_GET['type'] ?? '') == $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="bedrooms" class="form-select bg-light">
                    <option value="">Bedrooms</option>
                    <?php for($i=1; $i<=5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($_GET['bedrooms'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?>+ BHK</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Apply Filters</button>
                <a href="<?= BASE_URL ?>resell" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <?php if (empty($properties)): ?>
                <div class="col-12 text-center py-5" data-aos="fade-up">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted opacity-25"></i>
                    </div>
                    <h3 class="fw-bold">No properties found</h3>
                    <p class="text-muted">Try adjusting your filters or search terms to find what you're looking for.</p>
                    <a href="<?= BASE_URL ?>resell" class="btn btn-primary rounded-pill px-4 mt-3">View All Properties</a>
                </div>
            <?php else: ?>
                <?php foreach ($properties as $property): ?>
                    <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden property-card">
                            <div class="position-relative">
                                <?php if ($property['is_featured']): ?>
                                    <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3 z-1 px-3 py-2 rounded-pill shadow-sm">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </span>
                                <?php endif; ?>
                                <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 220px;">
                                    <i class="fas fa-home fa-4x text-muted opacity-25"></i>
                                </div>
                                <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark text-white">
                                    <h4 class="fw-bold mb-0">₹<?= number_format($property['price']) ?></h4>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill small"><?= ucfirst($property['property_type']) ?></span>
                                    <span class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i><?= $property['city'] ?></span>
                                </div>
                                <h5 class="fw-bold mb-3 text-truncate"><?= h($property['title']) ?></h5>
                                <div class="row g-2 mb-4">
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded-3 text-center">
                                            <i class="fas fa-bed text-primary mb-1"></i>
                                            <div class="small fw-bold"><?= $property['bedrooms'] ?> BHK</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded-3 text-center">
                                            <i class="fas fa-bath text-primary mb-1"></i>
                                            <div class="small fw-bold"><?= $property['bathrooms'] ?> Bath</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded-3 text-center">
                                            <i class="fas fa-ruler-combined text-primary mb-1"></i>
                                            <div class="small fw-bold"><?= number_format($property['area']) ?> ft²</div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary w-100 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#detailsModal<?= $property['id'] ?>">
                                    View Details <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                            <div class="card-footer bg-white border-top-0 p-4 pt-0">
                                <hr class="my-3 opacity-10">
                                <div class="d-flex align-items-center justify-content-between text-muted small">
                                    <span><i class="fas fa-user me-1"></i><?= h($property['full_name']) ?></span>
                                    <span><i class="fas fa-clock me-1"></i><?= date('M d, Y', strtotime($property['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Modal -->
                    <div class="modal fade" id="detailsModal<?= $property['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                                <div class="modal-header bg-primary text-white border-0 p-4">
                                    <h5 class="modal-title fw-bold"><?= h($property['title']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 p-md-5">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="bg-light rounded-4 d-flex align-items-center justify-content-center h-100" style="min-height: 250px;">
                                                <i class="fas fa-image fa-4x text-muted opacity-25"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <h3 class="fw-bold text-primary mb-0 me-3">₹<?= number_format($property['price']) ?></h3>
                                                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill"><?= ucfirst($property['property_type']) ?></span>
                                            </div>

                                            <div class="mb-4">
                                                <div class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i>Location</div>
                                                <p class="fw-bold mb-0"><?= h($property['address']) ?>, <?= $property['city'] ?>, <?= $property['state'] ?></p>
                                            </div>

                                            <div class="row g-3 mb-4">
                                                <div class="col-6">
                                                    <div class="p-3 bg-light rounded-3">
                                                        <div class="text-muted small mb-1">Bedrooms</div>
                                                        <div class="fw-bold"><?= $property['bedrooms'] ?> BHK</div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-3 bg-light rounded-3">
                                                        <div class="text-muted small mb-1">Bathrooms</div>
                                                        <div class="fw-bold"><?= $property['bathrooms'] ?> Bath</div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="p-3 bg-light rounded-3">
                                                        <div class="text-muted small mb-1">Total Area</div>
                                                        <div class="fw-bold"><?= number_format($property['area']) ?> sq.ft.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5">
                                        <h5 class="fw-bold mb-3">Description</h5>
                                        <p class="text-muted lead fs-6"><?= nl2br(h($property['description'])) ?></p>
                                    </div>

                                    <div class="mt-5 p-4 bg-light rounded-4">
                                        <h5 class="fw-bold mb-4">Seller Information</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                                <i class="fas fa-user fa-lg"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-1"><?= h($property['full_name']) ?></h6>
                                                <p class="text-muted small mb-0"><i class="fas fa-phone-alt me-1"></i>+91 <?= h($property['mobile']) ?></p>
                                            </div>
                                            <div class="ms-auto">
                                                <a href="https://wa.me/91<?= $property['mobile'] ?>?text=Hi! I'm interested in your property: <?= urlencode($property['title']) ?> - ₹<?= number_format($property['price']) ?> in <?= $property['city'] ?>"
                                                   class="btn btn-success rounded-pill px-4"
                                                   target="_blank">
                                                    <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-white border-top">
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <h2 class="display-6 fw-bold mb-4">Ready to Sell Your Property?</h2>
                <p class="lead text-muted mb-5">List your property for FREE and connect with genuine buyers directly without any middleman commissions.</p>
                <a href="<?= BASE_URL ?>/page/listProperty" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm">
                    <i class="fas fa-plus me-2"></i>List Your Property Now
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    .resell-hero-section {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }
    .property-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .property-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .bg-gradient-dark {
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
    }
    .bg-primary-subtle {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }
    .sticky-top {
        transition: all 0.3s ease;
    }
</style>
