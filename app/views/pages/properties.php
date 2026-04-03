

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                <?php if ($index === count($breadcrumbs) - 1): ?>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($breadcrumb['title']); ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>"><?php echo htmlspecialchars($breadcrumb['title']); ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-4 fw-bold text-primary">Properties</h1>
            <p class="lead text-muted">Browse our premium residential and commercial properties across Uttar Pradesh</p>
        </div>
    </div>

    <!-- Property Search Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Search Properties</h5>
            <form method="GET" action="<?php echo BASE_URL; ?>/properties" class="row g-3">
                <div class="col-md-3">
                    <label for="property_type" class="form-label">Property Type</label>
                    <select class="form-select" id="property_type" name="property_type">
                        <?php foreach ($property_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] === $type) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="location" class="form-label">Location</label>
                    <select class="form-select" id="location" name="location">
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo (isset($_GET['location']) && $_GET['location'] === $loc) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="price_range" class="form-label">Price Range</label>
                    <select class="form-select" id="price_range" name="price_range">
                        <?php foreach ($price_ranges as $price): ?>
                            <option value="<?php echo htmlspecialchars($price); ?>" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] === $price) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($price); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="bedrooms" class="form-label">Bedrooms</label>
                    <select class="form-select" id="bedrooms" name="bedrooms">
                        <?php foreach ($bedrooms as $bed): ?>
                            <option value="<?php echo htmlspecialchars($bed); ?>" <?php echo (isset($_GET['bedrooms']) && $_GET['bedrooms'] === $bed) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($bed); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Properties
                    </button>
                    <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-outline-secondary">Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="row">
        <?php if (!empty($properties)): ?>
            <?php foreach ($properties as $property): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 property-card">
                        <div class="property-image">
                            <img src="<?php echo BASE_URL; ?>/assets/images/properties/<?php echo htmlspecialchars($property['image']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($property['title']); ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="property-status">
                                <span class="badge bg-<?php echo $property['status'] === 'Available' ? 'success' : ($property['status'] === 'Coming Soon' ? 'warning' : 'info'); ?>">
                                    <?php echo htmlspecialchars($property['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($property['description']); ?></p>
                            <div class="property-details mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Area</small>
                                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($property['area']); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Bedrooms</small>
                                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($property['bedrooms']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="property-price">
                                    <h4 class="text-primary mb-0"><?php echo htmlspecialchars($property['price']); ?></h4>
                                </div>
                                <div class="property-type">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($property['type']); ?></span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>/properties/<?php echo $property['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No properties found matching your criteria.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <nav aria-label="Property pagination" class="mt-4">
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

<style>
.property-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.property-image {
    position: relative;
    overflow: hidden;
}

.property-status {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
}

.property-price h4 {
    font-size: 1.25rem;
    font-weight: 600;
}

.property-details .row {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

.property-details small {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.breadcrumb {
    background: transparent;
    padding: 0;
    margin-bottom: 1rem;
}

.card-body {
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .property-card {
        margin-bottom: 1rem;
    }
    
    .property-image img {
        height: 150px !important;
    }
}
</style>


