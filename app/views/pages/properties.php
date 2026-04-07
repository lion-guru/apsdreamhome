

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item active">Properties</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-building me-2"></i>Properties
            </h1>
            <p class="text-muted"><?php echo number_format($total); ?> properties found</p>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>/properties" class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">Property Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        <option value="plot" <?php echo $type === 'plot' ? 'selected' : ''; ?>>Plot</option>
                        <option value="house" <?php echo $type === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="flat" <?php echo $type === 'flat' ? 'selected' : ''; ?>>Flat/Apartment</option>
                        <option value="shop" <?php echo $type === 'shop' ? 'selected' : ''; ?>>Shop</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="listing" class="form-label">Listing Type</label>
                    <select class="form-select" id="listing" name="listing">
                        <option value="">Buy & Rent</option>
                        <option value="sell" <?php echo $listingType === 'sell' ? 'selected' : ''; ?>>For Sale</option>
                        <option value="rent" <?php echo $listingType === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="location" class="form-label">Location</label>
                    <select class="form-select" id="location" name="location">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo $location === $loc ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort" class="form-label">Sort By</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
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
                    <div class="card property-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo BASE_URL; ?>/assets/images/projects/gorakhpur/<?php echo htmlspecialchars($property['image'] ?? 'placeholder.jpg'); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($property['name']); ?>"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg'">
                            <div class="position-absolute top-0 end-0 p-2">
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
                                    <i class="fas fa-phone"></i> Enquire
                                </a>
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
                        <h5 class="text-muted">No properties found</h5>
                        <p class="text-muted">Try adjusting your filters or check back later.</p>
                        <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-primary">View All Properties</a>
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
                            Previous
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
                            Next
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

