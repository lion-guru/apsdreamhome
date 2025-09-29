<?php
/**
 * Enhanced Property Details Page
 * Complete property information with modern UI
 */

include '../app/views/includes/header.php';

// Get property ID from URL
$property_id = $_GET['id'] ?? 0;

if (!$property_id) {
    header('Location: /properties');
    exit;
}

// Initialize variables
$property = null;
$property_images = [];
$related_properties = [];
$agent_info = null;

// Fetch property details
try {
    $conn = getDbConnection();

    // Get main property information
    $query = "
        SELECT p.*, pt.name as property_type, pt.description as type_description,
               u.name as agent_name, u.email as agent_email, u.phone as agent_phone,
               u.profile_image as agent_image, u.bio as agent_bio,
               (SELECT AVG(rating) FROM property_reviews pr WHERE pr.property_id = p.id) as avg_rating,
               (SELECT COUNT(*) FROM property_reviews pr WHERE pr.property_id = p.id) as review_count
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        LEFT JOIN users u ON p.created_by = u.id
        WHERE p.id = ? AND p.status = 'available'
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        header('Location: /properties');
        exit;
    }

    // Get property images
    $images_query = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC";
    $images_stmt = $conn->prepare($images_query);
    $images_stmt->execute([$property_id]);
    $property_images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get related properties
    $related_query = "
        SELECT p.*, pt.name as property_type,
               (SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as main_image
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        WHERE p.id != ? AND p.property_type_id = ? AND p.status = 'available'
        ORDER BY p.created_at DESC LIMIT 3
    ";

    $related_stmt = $conn->prepare($related_query);
    $related_stmt->execute([$property_id, $property['property_type_id']]);
    $related_properties = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Error fetching property details: ' . $e->getMessage());
    $error_message = 'Unable to load property details. Please try again later.';
}
?>

<div class="container mt-4">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php else: ?>

    <!-- Property Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/properties">Properties</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($property['title']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Property Image Gallery -->
    <div class="row mb-5">
        <div class="col-lg-8">
            <div class="property-gallery">
                <div class="gallery-main">
                    <img src="<?php echo htmlspecialchars($property_images[0]['image_path'] ?? 'https://via.placeholder.com/800x600/667eea/ffffff?text=Property+Image'); ?>"
                         alt="<?php echo htmlspecialchars($property['title']); ?>"
                         class="gallery-main-img" id="mainImage">
                </div>
                <?php if (count($property_images) > 1): ?>
                <div class="gallery-thumbnails mt-3">
                    <?php foreach ($property_images as $index => $image): ?>
                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                         alt="Property view <?php echo $index + 1; ?>"
                         class="gallery-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                         onclick="changeMainImage('<?php echo htmlspecialchars($image['image_path']); ?>')">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Property Quick Info -->
        <div class="col-lg-4">
            <div class="property-quick-info card">
                <div class="card-body">
                    <h2 class="property-title mb-3"><?php echo htmlspecialchars($property['title']); ?></h2>

                    <div class="property-price mb-4">
                        <span class="price-main">₹<?php echo number_format($property['price']); ?></span>
                        <?php if ($property['price_per_sqft']): ?>
                        <span class="price-per-sqft">₹<?php echo number_format($property['price_per_sqft']); ?>/sq.ft</span>
                        <?php endif; ?>
                    </div>

                    <div class="property-features-grid mb-4">
                        <div class="feature-item">
                            <i class="fas fa-bed text-primary"></i>
                            <span><?php echo $property['bedrooms']; ?> Bedrooms</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-bath text-primary"></i>
                            <span><?php echo $property['bathrooms']; ?> Bathrooms</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-ruler-combined text-primary"></i>
                            <span><?php echo number_format($property['area_sqft']); ?> sq.ft</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-building text-primary"></i>
                            <span><?php echo htmlspecialchars($property['property_type']); ?></span>
                        </div>
                    </div>

                    <div class="property-actions mb-4">
                        <button class="btn btn-primary btn-lg w-100 mb-2" onclick="scheduleVisit(<?php echo $property['id']; ?>, '<?php echo htmlspecialchars($property['title']); ?>')">
                            <i class="fas fa-calendar-check me-2"></i>Schedule Visit
                        </button>
                        <button class="btn btn-outline-primary w-100 mb-2" onclick="toggleFavorite(<?php echo $property['id']; ?>)">
                            <i class="far fa-heart me-2"></i>Add to Favorites
                        </button>
                        <button class="btn btn-outline-secondary w-100" onclick="shareProperty(<?php echo $property['id']; ?>, '<?php echo htmlspecialchars($property['title']); ?>')">
                            <i class="fas fa-share-alt me-2"></i>Share Property
                        </button>
                    </div>

                    <!-- Agent Info -->
                    <?php if ($property['agent_name']): ?>
                    <div class="agent-card">
                        <div class="agent-avatar">
                            <img src="<?php echo htmlspecialchars($property['agent_image'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($property['agent_name'])); ?>"
                                 alt="Agent" class="agent-img">
                        </div>
                        <div class="agent-info">
                            <h6 class="agent-name"><?php echo htmlspecialchars($property['agent_name']); ?></h6>
                            <p class="agent-title">Property Agent</p>
                            <div class="agent-contact">
                                <a href="tel:<?php echo htmlspecialchars($property['agent_phone']); ?>" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-phone me-1"></i>Call
                                </a>
                                <a href="mailto:<?php echo htmlspecialchars($property['agent_email']); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i>Email
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Details Tabs -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="property-details-tabs">
                <ul class="nav nav-tabs" id="propertyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                            <i class="fas fa-info-circle me-2"></i>Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button">
                            <i class="fas fa-list me-2"></i>Features
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location" type="button">
                            <i class="fas fa-map-marked-alt me-2"></i>Location
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                            <i class="fas fa-star me-2"></i>Reviews (<?php echo $property['review_count'] ?? 0; ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="propertyTabsContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="property-overview p-4">
                            <h4>Property Overview</h4>
                            <p><?php echo nl2br(htmlspecialchars($property['description'] ?? 'Property description not available.')); ?></p>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h5>Property Details</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Property Type:</strong></td>
                                            <td><?php echo htmlspecialchars($property['property_type']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bedrooms:</strong></td>
                                            <td><?php echo $property['bedrooms']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bathrooms:</strong></td>
                                            <td><?php echo $property['bathrooms']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Area:</strong></td>
                                            <td><?php echo number_format($property['area_sqft']); ?> sq.ft</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Furnishing:</strong></td>
                                            <td><?php echo ucfirst($property['furnishing_status'] ?? 'Not specified'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Additional Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Listed:</strong></td>
                                            <td><?php echo date('M d, Y', strtotime($property['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Property ID:</strong></td>
                                            <td>#PROP<?php echo str_pad($property['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Parking:</strong></td>
                                            <td><?php echo $property['parking_spaces'] ?? 0; ?> spaces</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Floor:</strong></td>
                                            <td><?php echo $property['floor_number'] ?? 'Ground'; ?> floor</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features Tab -->
                    <div class="tab-pane fade" id="features" role="tabpanel">
                        <div class="property-features p-4">
                            <h4>Property Features</h4>
                            <div class="row">
                                <?php
                                $features = explode(',', $property['features'] ?? '');
                                foreach ($features as $feature):
                                    if (trim($feature)):
                                ?>
                                <div class="col-md-4 mb-3">
                                    <div class="feature-item">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <?php echo htmlspecialchars(trim($feature)); ?>
                                    </div>
                                </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Location Tab -->
                    <div class="tab-pane fade" id="location" role="tabpanel">
                        <div class="property-location p-4">
                            <h4>Location & Nearby</h4>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="location-map mb-4">
                                        <div style="height: 400px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <div class="text-center">
                                                <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                                                <h5>Interactive Map</h5>
                                                <p class="text-muted">Location: <?php echo htmlspecialchars($property['address']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h5>Nearby Amenities</h5>
                                    <div class="nearby-list">
                                        <div class="nearby-item">
                                            <i class="fas fa-school text-primary me-2"></i>
                                            <span>Schools within 2km</span>
                                        </div>
                                        <div class="nearby-item">
                                            <i class="fas fa-shopping-cart text-primary me-2"></i>
                                            <span>Shopping centers nearby</span>
                                        </div>
                                        <div class="nearby-item">
                                            <i class="fas fa-hospital text-primary me-2"></i>
                                            <span>Hospitals within 3km</span>
                                        </div>
                                        <div class="nearby-item">
                                            <i class="fas fa-bus text-primary me-2"></i>
                                            <span>Public transport available</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="property-reviews p-4">
                            <h4>Customer Reviews</h4>

                            <?php if ($property['review_count'] > 0): ?>
                            <div class="review-summary mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rating-display me-3">
                                        <span class="rating-number"><?php echo number_format($property['avg_rating'], 1); ?></span>
                                        <div class="stars">
                                            <?php
                                            $rating = $property['avg_rating'] ?? 0;
                                            for ($i = 1; $i <= 5; $i++):
                                                if ($i <= $rating):
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                elseif ($i - 0.5 <= $rating):
                                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                                else:
                                                    echo '<i class="far fa-star text-warning"></i>';
                                                endif;
                                            endfor;
                                            ?>
                                        </div>
                                    </div>
                                    <span class="review-count"><?php echo $property['review_count']; ?> reviews</span>
                                </div>
                            </div>

                            <div class="reviews-list">
                                <!-- Sample reviews - in real app, fetch from database -->
                                <div class="review-item mb-4">
                                    <div class="review-header">
                                        <strong>John Doe</strong>
                                        <div class="review-rating">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                        </div>
                                    </div>
                                    <p class="review-text">Excellent property with great amenities. The location is perfect and the agent was very helpful throughout the process.</p>
                                    <small class="text-muted">2 weeks ago</small>
                                </div>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">No reviews yet. Be the first to review this property!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Properties -->
    <?php if (!empty($related_properties)): ?>
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="section-title text-center mb-4">Similar Properties</h3>
            <div class="row">
                <?php foreach ($related_properties as $related): ?>
                <div class="col-md-4 mb-4">
                    <div class="card property-card h-100">
                        <img src="<?php echo htmlspecialchars($related['main_image'] ?? 'https://via.placeholder.com/400x250/667eea/ffffff?text=Property'); ?>"
                             class="card-img-top" alt="<?php echo htmlspecialchars($related['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($related['title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($related['address']); ?></p>
                            <div class="property-features">
                                <span><i class="fas fa-bed me-1"></i><?php echo $related['bedrooms']; ?> beds</span>
                                <span><i class="fas fa-bath me-1"></i><?php echo $related['bathrooms']; ?> baths</span>
                                <span><i class="fas fa-ruler-combined me-1"></i><?php echo number_format($related['area_sqft']); ?> sq.ft</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fw-bold">₹<?php echo number_format($related['price']); ?></span>
                                <a href="/property-details.php?id=<?php echo $related['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<script>
// Property gallery functionality
function changeMainImage(imageSrc) {
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.style.opacity = '0.5';
        setTimeout(() => {
            mainImage.src = imageSrc;
            mainImage.style.opacity = '1';
        }, 200);
    }

    // Update active thumbnail
    document.querySelectorAll('.gallery-thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Initialize property gallery
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth transitions to gallery
    const mainImage = document.getElementById('mainImage');
    if (mainImage) {
        mainImage.style.transition = 'opacity 0.3s ease';
    }
});
</script>

<?php include '../app/views/includes/footer.php'; ?>
