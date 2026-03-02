<?php
$page_title = $data['title'] ?? 'Properties - APS Dream Home';
$page_description = $data['description'] ?? 'Browse our exclusive collection of premium properties in Gorakhpur, Lucknow & across Uttar Pradesh';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-uppercase text-primary fw-bold">Property Listings</h6>
            <h2 class="display-5 fw-bold">Find Your Dream Property</h2>
            <div class="mx-auto bg-primary mt-3" style="height:4px;width:80px;border-radius:2px;"></div>
        </div>

        <!-- Search Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form action="<?php echo BASE_URL; ?>properties" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Property Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="apartment">Apartments</option>
                                    <option value="villa">Villas</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="plot">Plots / Land</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="City or Area">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Min Price</label>
                                <input type="number" name="min_price" class="form-control" placeholder="Min Price">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Price</label>
                                <input type="number" name="max_price" class="form-control" placeholder="Max Price">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Properties Grid -->
        <div class="row g-4">
            <?php if (!empty($data['properties'])): ?>
                <?php foreach ($data['properties'] as $property): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                            <div class="position-relative">
                                <img src="<?php echo !empty($property->image_path) ? htmlspecialchars($property->image_path) : BASE_URL . '/assets/images/property-placeholder.jpg'; ?>"
                                    class="card-img-top property-card-img"
                                    alt="<?php echo htmlspecialchars($property->title); ?>">
                                <?php if ($property->featured ?? false): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-3 px-3 py-2">Featured</span>
                                <?php endif; ?>
                                <span class="badge bg-dark position-absolute bottom-0 end-0 m-3 px-3 py-2">
                                    ₹<?php echo number_format($property->price); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-2">
                                    <a href="<?php echo BASE_URL; ?>properties/<?php echo $property->id; ?>" class="text-dark text-decoration-none stretched-link">
                                        <?php echo htmlspecialchars($property->title); ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i><?php echo htmlspecialchars($property->location); ?>
                                </p>
                                <div class="d-flex justify-content-between border-top pt-3 mt-3">
                                    <span class="small"><i class="fas fa-bed me-1"></i> <?php echo $property->bedrooms ?? 0; ?> Beds</span>
                                    <span class="small"><i class="fas fa-bath me-1"></i> <?php echo $property->bathrooms ?? 0; ?> Baths</span>
                                    <span class="small"><i class="fas fa-ruler-combined me-1"></i> <?php echo $property->area ?? 0; ?> Sq.ft</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted lead">No properties found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="text-center mt-5">
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h2 class="display-5 fw-bold mb-4">Can't Find What You're Looking For?</h2>
        <p class="lead mb-5 opacity-75">Let our experts help you find the perfect property.</p>
        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-light btn-lg px-5 rounded-pill text-primary fw-bold">Contact Us</a>
    </div>
</section>