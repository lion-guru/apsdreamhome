<?php
// app/views/pages/resell.php
// Available variables: $properties, $cities, $property_types, $filters, $pagination
?>

<!-- Hero Section -->
<section class="resell-hero text-center" style="background-image: url('<?= get_asset_url('assets/images/hero-1.jpg') ?>');">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 class="display-4 fw-bold mb-4">Resell Properties Marketplace</h1>
                <p class="lead mb-4">Buy directly from individual sellers. No brokerage. Verified properties. Trusted transactions.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>/list-property" class="btn btn-light btn-lg">
                        <i class="fas fa-plus me-2"></i>List Your Property
                    </a>
                    <a href="#properties" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-home me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Resell Properties</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Filters Section -->
<section class="container mt-n5 position-relative z-index-2">
    <div class="resell-filter-section shadow-sm bg-white p-4 rounded-3" style="margin-top: -50px;">
        <form action="<?= BASE_URL ?>/resell" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Locality, Landmark..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">City</label>
                <select name="city" class="form-select">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $city): ?>
                        <?php $cityValue = is_array($city) ? ($city['city'] ?? '') : ($city ?? ''); ?>
                        <option value="<?= htmlspecialchars($cityValue) ?>" <?= $filters['city'] == $cityValue ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cityValue ?: 'Unknown') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach ($property_types as $type): ?>
                        <?php $typeValue = is_array($type) ? ($type['type'] ?? '') : ($type ?? ''); ?>
                        <option value="<?= htmlspecialchars($typeValue) ?>" <?= $filters['type'] == $typeValue ? 'selected' : '' ?>>
                            <?= htmlspecialchars($typeValue ?: 'Unknown') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Min Price</label>
                <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= htmlspecialchars($filters['min_price']) ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Max Price</label>
                <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= htmlspecialchars($filters['max_price']) ?>">
            </div>

            <div class="col-12 text-end mt-4">
                <a href="<?= BASE_URL ?>/resell" class="btn btn-outline-secondary me-2">Reset</a>
                <button type="submit" class="btn btn-primary px-4">Apply Filters</button>
            </div>
        </form>
    </div>
</section>

<!-- Properties Grid -->
<section class="section-padding py-5" id="properties">
    <div class="container">
        <?php if (empty($properties)): ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-home fa-4x text-muted opacity-50"></i>
                </div>
                <h3>No Properties Found</h3>
                <p class="text-muted">Try adjusting your filters or search criteria.</p>
                <a href="<?= BASE_URL ?>/resell" class="btn btn-primary mt-3">View All Properties</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($properties as $prop): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card resell-property-card h-100 shadow-sm border-0 rounded-3 overflow-hidden">
                            <div class="position-relative">
                                <?php if ($prop->is_featured): ?>
                                    <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3 z-index-2">
                                        <i class="fas fa-star me-1"></i> Featured
                                    </span>
                                <?php endif; ?>

                                <span class="badge bg-primary position-absolute top-0 end-0 m-3 z-index-2">
                                    <?= htmlspecialchars($prop->status ?? 'Available') ?>
                                </span>

                                <img src="<?= !empty($prop->image) ? get_asset_url($prop->image) : 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=800&q=80' ?>" class="card-img-top resell-card-img" alt="<?= htmlspecialchars($prop->title) ?>">

                                <div class="price-badge position-absolute bottom-0 start-0 bg-dark text-white px-3 py-2 rounded-end-3 mb-3">
                                    ₹<?= number_format($prop->price) ?>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-2">
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1 text-primary"></i> <?= htmlspecialchars($prop->city) ?></small>
                                    <small class="text-muted ms-auto"><i class="fas fa-building me-1 text-primary"></i> <?= htmlspecialchars($prop->property_type) ?></small>
                                </div>

                                <h5 class="card-title mb-3 text-truncate"><?= htmlspecialchars($prop->title) ?></h5>

                                <div class="row g-2 mb-3 text-center small text-muted">
                                    <div class="col-4 border-end">
                                        <i class="fas fa-bed d-block mb-1 fa-lg"></i>
                                        <?= $prop->bedrooms ?> Beds
                                    </div>
                                    <div class="col-4 border-end">
                                        <i class="fas fa-bath d-block mb-1 fa-lg"></i>
                                        <?= $prop->bathrooms ?> Baths
                                    </div>
                                    <div class="col-4">
                                        <i class="fas fa-vector-square d-block mb-1 fa-lg"></i>
                                        <?= $prop->area ?> sqft
                                    </div>
                                </div>

                                <hr class="my-3 opacity-25">

                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-2">
                                            <i class="fas fa-user text-secondary"></i>
                                        </div>
                                        <small class="text-muted">
                                            Seller<br>
                                            <span class="text-dark fw-bold"><?= htmlspecialchars($prop->full_name ?? 'Verified Seller') ?></span>
                                        </small>
                                    </div>
                                    <a href="<?= BASE_URL ?>/property/<?= $prop->id ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if (isset($pagination['total_pages']) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="Page navigation" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?= $pagination['current_page'] == $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($filters, ['page' => ''])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>