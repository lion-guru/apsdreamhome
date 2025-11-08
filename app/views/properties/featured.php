<?php require_once 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-home mr-2"></i>
                    फीचर्ड प्रॉपर्टीज़
                </h1>
                <a href="/properties" class="btn btn-outline-primary">
                    <i class="fas fa-th-list mr-2"></i>सभी प्रॉपर्टीज़ देखें
                </a>
            </div>
        </div>
    </div>

    <!-- Featured Properties -->
    <div class="row">
        <?php if (empty($properties)): ?>
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-home fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">कोई फीचर्ड प्रॉपर्टी नहीं मिली</h4>
                        <p class="text-muted mb-4">अभी तक कोई प्रॉपर्टी फीचर्ड नहीं की गई है।</p>
                        <a href="/properties" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>प्रॉपर्टीज़ देखें
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($properties as $property): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow h-100 property-card">
                        <!-- Property Image -->
                        <div class="property-image-container">
                            <?php if ($property['featured_image']): ?>
                                <img src="<?= BASE_URL ?><?= htmlspecialchars($property['featured_image']) ?>"
                                     class="card-img-top property-image"
                                     alt="<?= htmlspecialchars($property['title']) ?>">
                            <?php else: ?>
                                <div class="property-image-placeholder">
                                    <i class="fas fa-home fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Property Status Badge -->
                            <div class="property-status-badge">
                                <span class="badge badge-<?= $this->getStatusBadgeClass($property['status'] ?? 'available') ?>">
                                    <?= $this->getStatusText($property['status'] ?? 'available') ?>
                                </span>
                            </div>

                            <!-- Featured Badge -->
                            <div class="property-featured-badge">
                                <span class="badge badge-warning">
                                    <i class="fas fa-star mr-1"></i>फीचर्ड
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Property Title -->
                            <h5 class="card-title property-title">
                                <a href="/properties/<?= $property['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($property['title']) ?>
                                </a>
                            </h5>

                            <!-- Property Location -->
                            <p class="card-text property-location">
                                <i class="fas fa-map-marker-alt mr-2 text-primary"></i>
                                <?= htmlspecialchars($property['location']) ?>
                            </p>

                            <!-- Property Price -->
                            <div class="property-price mb-3">
                                <span class="h5 text-success font-weight-bold">
                                    ₹<?= number_format($property['price']) ?>
                                </span>
                                <?php if ($property['type']): ?>
                                    <span class="badge badge-info ml-2">
                                        <?= htmlspecialchars($property['type']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Property Details -->
                            <div class="property-details mb-3">
                                <div class="row text-center">
                                    <?php if ($property['bedrooms']): ?>
                                        <div class="col-4">
                                            <i class="fas fa-bed text-muted"></i>
                                            <small class="d-block text-muted">
                                                <?= $property['bedrooms'] ?> बेडरूम
                                            </small>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($property['bathrooms']): ?>
                                        <div class="col-4">
                                            <i class="fas fa-bath text-muted"></i>
                                            <small class="d-block text-muted">
                                                <?= $property['bathrooms'] ?> बाथरूम
                                            </small>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($property['area']): ?>
                                        <div class="col-4">
                                            <i class="fas fa-ruler-combined text-muted"></i>
                                            <small class="d-block text-muted">
                                                <?= number_format($property['area']) ?> sq.ft
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Property Owner -->
                            <?php if ($property['owner_name']): ?>
                                <div class="property-owner mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user mr-1"></i>
                                        लिस्टेड बाय: <?= htmlspecialchars($property['owner_name']) ?>
                                    </small>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="property-actions">
                                <a href="/properties/<?= $property['id'] ?>"
                                   class="btn btn-primary btn-block">
                                    <i class="fas fa-eye mr-2"></i>विवरण देखें
                                </a>
                                <button type="button"
                                        class="btn btn-outline-secondary btn-block mt-2"
                                        onclick="contactOwner(<?= $property['id'] ?>)">
                                    <i class="fas fa-phone mr-2"></i>संपर्क करें
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Call to Action -->
    <?php if (!empty($properties)): ?>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-search mr-2"></i>
                            और प्रॉपर्टीज़ देखें
                        </h4>
                        <p class="card-text">
                            हजारों प्रॉपर्टीज़ में से अपनी पसंद की प्रॉपर्टी खोजें
                        </p>
                        <a href="/properties" class="btn btn-light btn-lg">
                            <i class="fas fa-th-list mr-2"></i>सभी प्रॉपर्टीज़ देखें
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function contactOwner(propertyId) {
    // Implement contact functionality
    alert('संपर्क फीचर जल्द आ रहा है!');
}
</script>

<style>
.property-card {
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.property-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.property-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.property-card:hover .property-image {
    transform: scale(1.05);
}

.property-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.property-status-badge {
    position: absolute;
    top: 10px;
    left: 10px;
}

.property-featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.property-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.property-title a {
    color: #2c3e50;
}

.property-title a:hover {
    color: #007bff;
    text-decoration: none;
}

.property-location {
    color: #6c757d;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.property-price {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.property-details {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 0.75rem;
}

.property-owner {
    padding: 0.5rem 0;
    border-top: 1px solid #e9ecef;
}

.property-actions .btn {
    border-radius: 6px;
    font-size: 0.9rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
