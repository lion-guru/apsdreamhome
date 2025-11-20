
<?php
// Get featured properties
$featured_properties_query = "SELECT * FROM properties WHERE featured = 1 LIMIT 6";
$featured_properties = $conn->query($featured_properties_query);

// Get latest properties
$latest_properties_query = "SELECT * FROM properties ORDER BY created_at DESC LIMIT 6";
$latest_properties = $conn->query($latest_properties_query);
?>

<?php include '../app/views/includes/header.php'; ?>

<section class="py-5 bg-light bg-gradient">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start">
                <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 rounded-pill mb-3">Smart property marketplace</span>
                <h1 class="display-5 fw-bold text-dark mb-3">Find Your Dream Property</h1>
                <p class="lead text-secondary mb-4">Discover handpicked homes, villas and commercial spaces curated for modern living across India.</p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                    <a href="/properties" class="btn btn-primary btn-lg px-4">Browse Properties</a>
                    <a href="/contact" class="btn btn-outline-primary btn-lg px-4">Talk to an Advisor</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary-subtle text-primary-emphasis">Smart Search</span>
                            <span class="text-muted small">Tailored results based on your preferences</span>
                        </div>
                        <form action="/properties" method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="location" class="form-label fw-semibold text-secondary">Location</label>
                                <input type="text" class="form-control form-control-lg" id="location" name="location" placeholder="City or project">
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label fw-semibold text-secondary">Property Type</label>
                                <select class="form-select form-select-lg" id="type" name="type">
                                    <option value="">Any</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="land">Land / Plot</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="min_price" class="form-label fw-semibold text-secondary">Min Price</label>
                                <input type="number" class="form-control" id="min_price" name="min_price" placeholder="e.g. 2500000">
                            </div>
                            <div class="col-md-6">
                                <label for="max_price" class="form-label fw-semibold text-secondary">Max Price</label>
                                <input type="number" class="form-control" id="max_price" name="max_price" placeholder="e.g. 9000000">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100">Search Properties</button>
                                <p class="text-muted small text-center mt-3 mb-0">Filter by amenities, budget and preferred localities. 500+ listings updated weekly.</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h2 class="h3 fw-semibold mb-1">Featured Properties</h2>
                <p class="text-muted mb-0">Premium listings handpicked by our real-estate experts</p>
            </div>
            <a href="/properties" class="btn btn-outline-primary">View All</a>
        </div>

        <?php if ($featured_properties && $featured_properties->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <?php while ($property = $featured_properties->fetch_assoc()): ?>
                    <div class="col">
                        <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="position-relative">
                                <img src="/uploads/properties/<?php echo $property['image']; ?>" class="card-img-top object-fit-cover" style="height: 240px;" alt="<?php echo $property['title']; ?>">
                                <?php if (!empty($property['featured'])): ?>
                                    <span class="badge bg-warning text-dark fw-semibold position-absolute top-0 start-0 m-3 rounded-pill px-3 py-2">Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-4">
                                <h3 class="h5 fw-semibold mb-2"><?php echo $property['title']; ?></h3>
                                <p class="text-muted mb-3"><i class="fas fa-map-marker-alt text-primary me-2"></i><?php echo $property['location']; ?></p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-primary fs-5"><?php echo Helpers::formatCurrency($property['price']); ?></span>
                                    <span class="badge bg-primary-subtle text-primary-emphasis">Ready to Move</span>
                                </div>
                                <div class="d-flex gap-3 text-secondary small">
                                    <span><i class="fas fa-bed me-1"></i><?php echo $property['bedrooms']; ?> Beds</span>
                                    <span><i class="fas fa-bath me-1"></i><?php echo $property['bathrooms']; ?> Baths</span>
                                    <span><i class="fas fa-vector-square me-1"></i><?php echo $property['area']; ?> sq.ft</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 px-4 pb-4">
                                <a href="/property/<?php echo $property['id']; ?>" class="btn btn-primary w-100">View Details</a>
                            </div>
                        </article>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                No featured properties available at the moment. Check back soon!
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h2 class="h3 fw-semibold mb-1">Latest Listings</h2>
                <p class="text-muted mb-0">Stay ahead with newly added properties across top metros</p>
            </div>
            <a href="/properties?sort=latest" class="btn btn-outline-secondary">See Newest First</a>
        </div>

        <?php if ($latest_properties && $latest_properties->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <?php while ($property = $latest_properties->fetch_assoc()): ?>
                    <div class="col">
                        <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="position-relative">
                                <img src="/uploads/properties/<?php echo $property['image']; ?>" class="card-img-top object-fit-cover" style="height: 220px;" alt="<?php echo $property['title']; ?>">
                            </div>
                            <div class="card-body p-4">
                                <h3 class="h5 fw-semibold mb-2"><?php echo $property['title']; ?></h3>
                                <p class="text-muted mb-3"><i class="fas fa-map-marker-alt text-primary me-2"></i><?php echo $property['location']; ?></p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-primary fs-5"><?php echo Helpers::formatCurrency($property['price']); ?></span>
                                    <span class="badge bg-success-subtle text-success-emphasis">New</span>
                                </div>
                                <div class="d-flex gap-3 text-secondary small">
                                    <span><i class="fas fa-bed me-1"></i><?php echo $property['bedrooms']; ?> Beds</span>
                                    <span><i class="fas fa-bath me-1"></i><?php echo $property['bathrooms']; ?> Baths</span>
                                    <span><i class="fas fa-vector-square me-1"></i><?php echo $property['area']; ?> sq.ft</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 px-4 pb-4">
                                <a href="/property/<?php echo $property['id']; ?>" class="btn btn-outline-primary w-100">View Details</a>
                            </div>
                        </article>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border rounded-4" role="alert">
                No new properties available right now. Configure alerts to get notified instantly.
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../app/views/includes/footer.php'; ?>