<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}
include __DIR__ . '/../layouts/header.php';

$locationValue = htmlspecialchars($_GET['location'] ?? '');
$typeValue = $_GET['type'] ?? '';
$minPrice = htmlspecialchars($_GET['min_price'] ?? '');
$maxPrice = htmlspecialchars($_GET['max_price'] ?? '');
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 rounded-pill mb-2">Property marketplace</span>
            <h1 class="h3 fw-semibold mb-1">Explore Properties</h1>
            <p class="text-secondary mb-0">Browse verified listings across premium locations with transparent pricing.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                <i class="fas fa-sliders-h me-2"></i>Filters
            </button>
            <a href="/properties?sort=latest" class="btn btn-primary">
                <i class="fas fa-sort-amount-down me-2"></i>Sort by Latest
            </a>
        </div>
    </div>

    <div class="row">
        <aside class="col-lg-3 d-none d-lg-block">
            <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 80px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="/properties" class="vstack gap-3">
                        <div>
                            <label for="location" class="form-label fw-semibold text-secondary">Location</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="City or area" value="<?php echo $locationValue; ?>">
                        </div>
                        <div>
                            <label for="type" class="form-label fw-semibold text-secondary">Property type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="" <?php echo $typeValue === '' ? 'selected' : ''; ?>>All types</option>
                                <option value="residential" <?php echo $typeValue === 'residential' ? 'selected' : ''; ?>>Residential</option>
                                <option value="commercial" <?php echo $typeValue === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                <option value="plot" <?php echo $typeValue === 'plot' ? 'selected' : ''; ?>>Plot / Land</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-secondary">Price range (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">Min</span>
                                <input type="number" class="form-control" name="min_price" placeholder="25,00,000" value="<?php echo $minPrice; ?>">
                            </div>
                            <div class="input-group mt-2">
                                <span class="input-group-text">Max</span>
                                <input type="number" class="form-control" name="max_price" placeholder="1,50,00,000" value="<?php echo $maxPrice; ?>">
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-secondary">Amenities</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="amenity-parking" disabled>
                                <label class="form-check-label" for="amenity-parking">Reserved parking</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="amenity-clubhouse" disabled>
                                <label class="form-check-label" for="amenity-clubhouse">Clubhouse access</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="amenity-security" disabled>
                                <label class="form-check-label" for="amenity-security">24/7 security</label>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Apply filters</button>
                            <a href="/properties" class="btn btn-outline-secondary">Clear all</a>
                        </div>
                    </form>
                </div>
            </div>
        </aside>

        <main class="col-lg-9">
            <!-- Mobile filters -->
            <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <form method="GET" action="/properties" class="vstack gap-3">
                        <div>
                            <label for="location-mobile" class="form-label fw-semibold text-secondary">Location</label>
                            <input type="text" class="form-control" id="location-mobile" name="location" value="<?php echo $locationValue; ?>">
                        </div>
                        <div>
                            <label for="type-mobile" class="form-label fw-semibold text-secondary">Property type</label>
                            <select class="form-select" id="type-mobile" name="type">
                                <option value="" <?php echo $typeValue === '' ? 'selected' : ''; ?>>All types</option>
                                <option value="residential" <?php echo $typeValue === 'residential' ? 'selected' : ''; ?>>Residential</option>
                                <option value="commercial" <?php echo $typeValue === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                <option value="plot" <?php echo $typeValue === 'plot' ? 'selected' : ''; ?>>Plot / Land</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-secondary">Price range (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">Min</span>
                                <input type="number" class="form-control" name="min_price" value="<?php echo $minPrice; ?>">
                            </div>
                            <div class="input-group mt-2">
                                <span class="input-group-text">Max</span>
                                <input type="number" class="form-control" name="max_price" value="<?php echo $maxPrice; ?>">
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Apply filters</button>
                            <a href="/properties" class="btn btn-outline-secondary">Clear all</a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($properties)): ?>
                <div class="row g-4">
                    <?php foreach ($properties as $property): ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <article class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                <div class="position-relative" style="height: 220px;">
                                    <?php if (!empty($property['image'])): ?>
                                        <img src="/uploads/properties/<?php echo htmlspecialchars($property['image']); ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                    <?php else: ?>
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                                            <i class="fas fa-home fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="badge bg-primary-subtle text-primary-emphasis position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill">
                                        <?php echo ucfirst(htmlspecialchars($property['type'])); ?>
                                    </span>
                                </div>
                                <div class="card-body p-4">
                                    <h3 class="h5 fw-semibold mb-2"><?php echo htmlspecialchars($property['title']); ?></h3>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i><?php echo htmlspecialchars($property['location']); ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-bold text-primary fs-5"><?php echo Helpers::formatCurrency($property['price']); ?></span>
                                        <?php if (!empty($property['status'])): ?>
                                            <span class="badge bg-success-subtle text-success-emphasis"><?php echo htmlspecialchars($property['status']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-3 text-secondary small">
                                        <?php if (!empty($property['bedrooms'])): ?>
                                            <span><i class="fas fa-bed me-1"></i><?php echo htmlspecialchars($property['bedrooms']); ?> Beds</span>
                                        <?php endif; ?>
                                        <?php if (!empty($property['bathrooms'])): ?>
                                            <span><i class="fas fa-bath me-1"></i><?php echo htmlspecialchars($property['bathrooms']); ?> Baths</span>
                                        <?php endif; ?>
                                        <?php if (!empty($property['area'])): ?>
                                            <span><i class="fas fa-vector-square me-1"></i><?php echo htmlspecialchars($property['area']); ?> sq.ft</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 px-4 pb-4">
                                    <div class="d-grid gap-2">
                                        <a href="/properties/<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <a href="/properties/<?php echo $property['id']; ?>/contact" class="btn btn-outline-primary">Contact Agent</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info border-0 rounded-4" role="alert">
                    <i class="fas fa-info-circle me-2"></i>No properties found matching your criteria.
                    <a href="/properties" class="ms-2 text-decoration-none">Reset filters</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($properties) && isset($pagination)): ?>
                <nav class="mt-4" aria-label="Property pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $pagination['base_url'] . '&page=' . ($pagination['current_page'] - 1); ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                            <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $pagination['base_url'] . '&page=' . $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $pagination['base_url'] . '&page=' . ($pagination['current_page'] + 1); ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>