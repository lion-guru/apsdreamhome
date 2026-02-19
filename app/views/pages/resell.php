
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4rem 0;
        margin-bottom: 3rem;
        border-radius: 0 0 20px 20px;
    }
    .property-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #e0e0e0;
        border-radius: 15px;
        overflow: hidden;
        height: 100%;
        background: white;
    }
    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .featured-badge {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 10;
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    .price-tag {
        font-size: 1.5rem;
        font-weight: 700;
        color: #28a745;
    }
    .whatsapp-btn {
        background: #25D366;
        color: white;
        border: none;
        width: 100%;
    }
    .whatsapp-btn:hover {
        background: #128C7E;
        color: white;
    }
    .property-details {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .property-details i {
        width: 20px;
        text-align: center;
        margin-right: 5px;
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">Resell Properties Marketplace</h1>
                <p class="lead mb-4">Buy directly from individual sellers. No brokerage. Verified properties. Trusted transactions.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>list-property" class="btn btn-light btn-lg">
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

<!-- Filters Section -->
<section class="container">
    <div class="filter-section shadow-sm">
        <form action="<?= BASE_URL ?>resell" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Locality, Landmark..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>
            
            <div class="col-md-2">
                <label class="form-label fw-bold">City</label>
                <select name="city" class="form-select">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= htmlspecialchars($city['city']) ?>" <?= $filters['city'] == $city['city'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($city['city']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach ($property_types as $type): ?>
                        <option value="<?= htmlspecialchars($type['property_type']) ?>" <?= $filters['type'] == $type['property_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['property_type']) ?>
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

            <div class="col-12 text-end">
                <a href="<?= BASE_URL ?>resell" class="btn btn-outline-secondary me-2">Reset</a>
                <button type="submit" class="btn btn-primary px-4">Apply Filters</button>
            </div>
        </form>
    </div>
</section>

<!-- Properties Grid -->
<section class="container mb-5" id="properties">
    <?php if (empty($properties)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-home fa-4x text-muted"></i>
            </div>
            <h3>No Properties Found</h3>
            <p class="text-muted">Try adjusting your filters or search criteria.</p>
            <a href="<?= BASE_URL ?>resell" class="btn btn-primary mt-3">View All Properties</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($properties as $prop): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card property-card">
                        <div class="position-relative">
                            <?php if ($prop['is_featured']): ?>
                                <span class="featured-badge">
                                    <i class="fas fa-star me-1"></i> Featured
                                </span>
                            <?php endif; ?>
                            
                            <!-- Placeholder image if no image functionality yet or use static placeholder -->
                            <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="<?= htmlspecialchars($prop['title']) ?>" style="height: 250px; object-fit: cover;">
                            
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                                <h5 class="mb-0 text-white text-shadow"><?= htmlspecialchars($prop['city']) ?></h5>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title text-truncate" title="<?= htmlspecialchars($prop['title']) ?>">
                                <?= htmlspecialchars($prop['title']) ?>
                            </h5>
                            <p class="card-text text-muted small mb-3">
                                <i class="fas fa-map-marker-alt me-1 text-primary"></i> 
                                <?= htmlspecialchars($prop['address']) ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="price-tag">₹<?= number_format($prop['price']) ?></span>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($prop['property_type']) ?>
                                </span>
                            </div>
                            
                            <div class="row g-2 mb-3 property-details">
                                <div class="col-6">
                                    <i class="fas fa-bed"></i> <?= $prop['bedrooms'] ?> Beds
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-bath"></i> <?= $prop['bathrooms'] ?> Baths
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-ruler-combined"></i> <?= $prop['area'] ?> sq ft
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-clock"></i> <?= date('M d', strtotime($prop['created_at'])) ?>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <?= strtoupper(substr($prop['full_name'], 0, 1)) ?>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 small fw-bold"><?= htmlspecialchars($prop['full_name']) ?></h6>
                                    <small class="text-muted">Property Owner</small>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="https://wa.me/91<?= $prop['mobile'] ?>?text=Hi, I'm interested in your property: <?= urlencode($prop['title']) ?>" target="_blank" class="btn whatsapp-btn">
                                    <i class="fab fa-whatsapp me-2"></i> Chat with Owner
                                </a>
                                <a href="tel:<?= $prop['mobile'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-phone me-2"></i> Call Owner
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
