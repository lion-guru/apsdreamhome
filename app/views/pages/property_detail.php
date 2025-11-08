<?php
/**
 * Property Detail Page Template
 * Displays detailed information about a single property
 */

?>

<!-- Property Hero Section -->
<section class="property-hero py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>properties">Properties</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($property['title']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row g-4">
            <!-- Property Images -->
            <div class="col-lg-8">
                <div class="property-images">
                    <?php if (!empty($property_images)): ?>
                        <div class="main-image mb-3">
                            <img src="<?php echo htmlspecialchars($property_images[0]['image_path']); ?>"
                                 alt="<?php echo htmlspecialchars($property['title']); ?>"
                                 class="img-fluid rounded shadow"
                                 id="mainPropertyImage">
                        </div>
                        <?php if (count($property_images) > 1): ?>
                            <div class="image-thumbnails">
                                <div class="row g-2">
                                    <?php foreach (array_slice($property_images, 0, 6) as $image): ?>
                                        <div class="col-2">
                                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                                 alt="Property thumbnail"
                                                 class="img-fluid rounded cursor-pointer thumbnail-image"
                                                 onclick="changeMainImage('<?php echo htmlspecialchars($image['image_path']); ?>')">
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($property_images) > 6): ?>
                                        <div class="col-2">
                                            <div class="img-fluid rounded d-flex align-items-center justify-content-center bg-light more-images">
                                                <span class="text-muted">+<?php echo count($property_images) - 6; ?> more</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="main-image mb-3">
                            <img src="https://via.placeholder.com/800x500/667eea/ffffff?text=<?php echo urlencode($property['title']); ?>"
                                 alt="<?php echo htmlspecialchars($property['title']); ?>"
                                 class="img-fluid rounded shadow">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Property Details -->
            <div class="col-lg-4">
                <div class="property-details-card">
                    <div class="property-header">
                        <h1 class="property-title mb-3"><?php echo htmlspecialchars($property['title']); ?></h1>

                        <div class="property-price mb-3">
                            <span class="h2 text-primary fw-bold">
                                <?php
                                $price = $property['price'] ?? 0;
                                echo $price > 0 ? '₹' . number_format($price) : 'Price on Request';
                                ?>
                            </span>
                            <?php if ($property['featured']): ?>
                                <span class="badge bg-warning ms-2">
                                    <i class="fas fa-star me-1"></i>Featured
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="property-location mb-4">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            <?php echo htmlspecialchars($property['address'] ?? 'Location not specified'); ?>
                        </div>
                    </div>

                    <!-- Quick Features -->
                    <div class="property-features mb-4">
                        <div class="row g-3">
                            <?php if (!empty($property['bedrooms'])): ?>
                                <div class="col-4">
                                    <div class="feature-item text-center">
                                        <i class="fas fa-bed fa-2x text-primary mb-2"></i>
                                        <div class="fw-bold"><?php echo $property['bedrooms']; ?></div>
                                        <small class="text-muted">Bedrooms</small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($property['bathrooms'])): ?>
                                <div class="col-4">
                                    <div class="feature-item text-center">
                                        <i class="fas fa-bath fa-2x text-success mb-2"></i>
                                        <div class="fw-bold"><?php echo $property['bathrooms']; ?></div>
                                        <small class="text-muted">Bathrooms</small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($property['area_sqft'])): ?>
                                <div class="col-4">
                                    <div class="feature-item text-center">
                                        <i class="fas fa-ruler-combined fa-2x text-info mb-2"></i>
                                        <div class="fw-bold"><?php echo number_format($property['area_sqft']); ?></div>
                                        <small class="text-muted">Sq.Ft</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="property-actions">
                        <?php
                        // Check if user is logged in and if property is favorited
                        $isFavorited = false;
                        $favoriteClass = 'btn-outline-secondary';
                        $favoriteIcon = 'fa-heart';
                        $favoriteText = 'Save';

                        if (isset($_SESSION['user_id'])) {
                            // Check if this property is in user's favorites
                            try {
                                global $pdo;
                                if ($pdo) {
                                    $stmt = $pdo->prepare("SELECT id FROM property_favorites WHERE user_id = ? AND property_id = ?");
                                    $stmt->execute([$_SESSION['user_id'], $property['id']]);
                                    if ($stmt->rowCount() > 0) {
                                        $isFavorited = true;
                                        $favoriteClass = 'btn-danger';
                                        $favoriteIcon = 'fa-heart-broken';
                                        $favoriteText = 'Remove from Favorites';
                                    }
                                }
                            } catch (Exception $e) {
                                // Ignore errors for now
                            }
                        }
                        ?>

                        <div class="d-flex gap-2 mb-3">
                            <button class="btn <?php echo $favoriteClass; ?> flex-fill favorite-toggle"
                                    data-property-id="<?php echo $property['id']; ?>"
                                    data-is-favorited="<?php echo $isFavorited ? '1' : '0'; ?>">
                                <i class="fas <?php echo $favoriteIcon; ?> me-2"></i><?php echo $favoriteText; ?>
                            </button>
                            <button class="btn btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#inquiryModal">
                                <i class="fas fa-envelope me-2"></i>Inquire
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-info flex-fill" onclick="shareProperty()">
                                <i class="fas fa-share-alt me-2"></i>Share
                            </button>
                            <a href="#contact-form" class="btn btn-primary flex-fill">
                                <i class="fas fa-phone-alt me-2"></i>Contact Agent
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Information Tabs -->
<section class="property-info py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs" id="propertyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                                type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i>Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features"
                                type="button" role="tab">
                            <i class="fas fa-list-ul me-2"></i>Features
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location"
                                type="button" role="tab">
                            <i class="fas fa-map me-2"></i>Location
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="agent-tab" data-bs-toggle="tab" data-bs-target="#agent"
                                type="button" role="tab">
                            <i class="fas fa-user-tie me-2"></i>Agent
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-4" id="propertyTabsContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <h3>Property Description</h3>
                                <p class="lead">
                                    <?php echo htmlspecialchars($property['description'] ?? 'No description available for this property.'); ?>
                                </p>

                                <div class="property-highlights mt-4">
                                    <h5>Property Highlights</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="highlight-item">
                                                <i class="fas fa-calendar text-primary me-2"></i>
                                                <span>Listed: <?php echo date('F j, Y', strtotime($property['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="highlight-item">
                                                <i class="fas fa-tag text-success me-2"></i>
                                                <span>Type: <?php echo htmlspecialchars($property['property_type'] ?? 'Property'); ?></span>
                                            </div>
                                        </div>
                                        <?php if (!empty($property['city'])): ?>
                                            <div class="col-md-6">
                                                <div class="highlight-item">
                                                    <i class="fas fa-map-marker-alt text-info me-2"></i>
                                                    <span>City: <?php echo htmlspecialchars($property['city']); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($property['state'])): ?>
                                            <div class="col-md-6">
                                                <div class="highlight-item">
                                                    <i class="fas fa-globe text-warning me-2"></i>
                                                    <span>State: <?php echo htmlspecialchars($property['state']); ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="property-summary">
                                    <h5>Property Summary</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="text-muted">Property ID:</td>
                                            <td class="fw-bold">#<?php echo $property['id']; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status:</td>
                                            <td><span class="badge bg-success">Available</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Listed:</td>
                                            <td><?php echo date('M d, Y', strtotime($property['created_at'])); ?></td>
                                        </tr>
                                        <?php if (!empty($property['bedrooms'])): ?>
                                            <tr>
                                                <td class="text-muted">Bedrooms:</td>
                                                <td><?php echo $property['bedrooms']; ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if (!empty($property['bathrooms'])): ?>
                                            <tr>
                                                <td class="text-muted">Bathrooms:</td>
                                                <td><?php echo $property['bathrooms']; ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if (!empty($property['area_sqft'])): ?>
                                            <tr>
                                                <td class="text-muted">Area:</td>
                                                <td><?php echo number_format($property['area_sqft']); ?> sq.ft</td>
                                            </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features Tab -->
                    <div class="tab-pane fade" id="features" role="tabpanel">
                        <h3>Property Features</h3>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h5>Interior Features</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Modern Kitchen</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Spacious Living Area</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Built-in Wardrobes</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Air Conditioning</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Ceiling Fans</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Exterior Features</h5>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Private Parking</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Garden Area</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Security System</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Balcony/Terrace</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Good Ventilation</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Location Tab -->
                    <div class="tab-pane fade" id="location" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <h3>Location & Nearby</h3>
                                <div class="location-info">
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($property['address']); ?></p>

                                    <?php if (!empty($property['city'])): ?>
                                        <p><strong>City:</strong> <?php echo htmlspecialchars($property['city']); ?></p>
                                    <?php endif; ?>

                                    <?php if (!empty($property['state'])): ?>
                                        <p><strong>State:</strong> <?php echo htmlspecialchars($property['state']); ?></p>
                                    <?php endif; ?>

                                    <div class="nearby-facilities mt-4">
                                        <h5>Nearby Facilities</h5>
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <div class="facility-item">
                                                    <i class="fas fa-school text-primary me-2"></i>
                                                    <span>Educational Institutions</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="facility-item">
                                                    <i class="fas fa-shopping-cart text-success me-2"></i>
                                                    <span>Shopping Centers</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="facility-item">
                                                    <i class="fas fa-hospital text-danger me-2"></i>
                                                    <span>Hospitals & Clinics</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="facility-item">
                                                    <i class="fas fa-utensils text-warning me-2"></i>
                                                    <span>Restaurants & Cafes</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <!-- Map placeholder -->
                                <div class="map-container">
                                    <iframe
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.123456789!2d83.123456!3d26.123456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39915c7c7c7c7c7c%3A0x7c7c7c7c7c7c7c7c!2sGorakhpur%2C%20Uttar%20Pradesh!5e0!3m2!1sen!2sin!4v1234567890123!5m2!1sen!2sin"
                                        width="100%"
                                        height="300"
                                        style="border:0; border-radius: 10px;"
                                        allowfullscreen=""
                                        loading="lazy">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agent Tab -->
                    <div class="tab-pane fade" id="agent" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <h3>Contact Agent</h3>
                                <div class="agent-info-card">
                                    <div class="agent-avatar mb-3">
                                        <img src="https://via.placeholder.com/100x100/667eea/ffffff?text=Agent"
                                             alt="Property Agent"
                                             class="img-fluid rounded-circle">
                                    </div>
                                    <h5><?php echo htmlspecialchars($property['agent_name'] ?? 'Property Agent'); ?></h5>
                                    <p class="text-muted mb-3">Licensed Real Estate Agent</p>

                                    <div class="agent-contact">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-phone text-primary me-2"></i>
                                            <a href="tel:<?php echo htmlspecialchars($property['agent_phone'] ?? '+91-1234567890'); ?>">
                                                <?php echo htmlspecialchars($property['agent_phone'] ?? '+91-1234567890'); ?>
                                            </a>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-envelope text-success me-2"></i>
                                            <a href="mailto:<?php echo htmlspecialchars($property['agent_email'] ?? 'agent@example.com'); ?>">
                                                <?php echo htmlspecialchars($property['agent_email'] ?? 'agent@example.com'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <h3>Send Message to Agent</h3>
                                <form id="contact-form">
                                    <div class="mb-3">
                                        <label for="agent-name" class="form-label">Your Name</label>
                                        <input type="text" class="form-control" id="agent-name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="agent-email" class="form-label">Your Email</label>
                                        <input type="email" class="form-control" id="agent-email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="agent-phone" class="form-label">Your Phone</label>
                                        <input type="tel" class="form-control" id="agent-phone">
                                    </div>
                                    <div class="mb-3">
                                        <label for="agent-message" class="form-label">Message</label>
                                        <textarea class="form-control" id="agent-message" rows="4"
                                                  placeholder="I'm interested in this property. Please contact me with more details."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Properties -->
<?php if (!empty($related_properties)): ?>
<section class="related-properties py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="section-title text-center mb-4">
                    <i class="fas fa-th-large text-primary me-2"></i>
                    Related Properties
                </h3>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($related_properties as $related): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="property-card">
                        <div class="property-image-container">
                            <?php if ($related['main_image']): ?>
                                <img src="<?php echo htmlspecialchars($related['main_image']); ?>"
                                     alt="<?php echo htmlspecialchars($related['title']); ?>"
                                     class="property-image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x200/667eea/ffffff?text=Property"
                                     alt="<?php echo htmlspecialchars($related['title']); ?>"
                                     class="property-image">
                            <?php endif; ?>

                            <div class="property-overlay">
                                <a href="<?php echo BASE_URL; ?>property?id=<?php echo $related['id']; ?>"
                                   class="btn btn-light btn-sm">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </div>
                        </div>

                        <div class="property-content">
                            <h6 class="property-title">
                                <a href="<?php echo BASE_URL; ?>property?id=<?php echo $related['id']; ?>">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </a>
                            </h6>
                            <div class="property-price">
                                <?php
                                $price = $related['price'] ?? 0;
                                echo $price > 0 ? '₹' . number_format($price) : 'Price on Request';
                                ?>
                            </div>
                            <div class="property-location small text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php echo htmlspecialchars($related['city'] ?? 'Location not specified'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action -->
<section class="cta-property-detail py-5" style="background: linear-gradient(135deg, #1a237e 0%, #667eea 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h3 class="text-white mb-3">
                    <i class="fas fa-comments me-2"></i>
                    Interested in This Property?
                </h3>
                <p class="text-white-50 mb-0">
                    Contact our expert agents today to schedule a viewing or get more information.
                    We're here to help you make the right choice for your dream home.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#contact-form" class="btn btn-warning btn-lg px-4 py-3 me-3">
                    <i class="fas fa-phone-alt me-2"></i>Contact Agent
                </a>
                <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-light btn-lg px-4 py-3">
                    <i class="fas fa-search me-2"></i>More Properties
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Property Inquiry Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inquiryModalLabel">
                    <i class="fas fa-envelope me-2"></i>
                    Inquire About This Property
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="inquiry-form">
                    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <!-- Guest form fields -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guest-name" class="form-label">Your Name *</label>
                                <input type="text" class="form-control" id="guest-name" name="guest_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="guest-email" class="form-label">Your Email *</label>
                                <input type="email" class="form-control" id="guest-email" name="guest_email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="guest-phone" class="form-label">Your Phone</label>
                            <input type="tel" class="form-control" id="guest-phone" name="guest_phone">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="inquiry-subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control" id="inquiry-subject" name="subject"
                               value="Inquiry about <?php echo htmlspecialchars($property['title']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="inquiry-type" class="form-label">Inquiry Type</label>
                        <select class="form-select" id="inquiry-type" name="inquiry_type">
                            <option value="general">General Inquiry</option>
                            <option value="viewing">Schedule Viewing</option>
                            <option value="price">Price Information</option>
                            <option value="availability">Availability Check</option>
                            <option value="offer">Make an Offer</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="inquiry-message" class="form-label">Message *</label>
                        <textarea class="form-control" id="inquiry-message" name="message" rows="4"
                                  placeholder="Please let me know more about this property. I'm particularly interested in..." required></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Our agents will respond to your inquiry within 24 hours.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submit-inquiry">
                    <i class="fas fa-paper-plane me-2"></i>Submit Inquiry
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function changeMainImage(imageSrc) {
    document.getElementById('mainPropertyImage').src = imageSrc;
}

function shareProperty() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($property['title']); ?>',
            text: 'Check out this amazing property!',
            url: window.location.href
        });
    } else {
        // Fallback for browsers that don't support Web Share API
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('Property link copied to clipboard!');
        });
    }
}

// Favorite toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const favoriteButtons = document.querySelectorAll('.favorite-toggle');

    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.getAttribute('data-property-id');
            const isFavorited = this.getAttribute('data-is-favorited') === '1';
            const button = this;

            // Show loading state
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            button.disabled = true;

            fetch('<?php echo BASE_URL; ?>favorites/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'property_id=' + propertyId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button state
                    if (data.is_favorited) {
                        button.className = 'btn btn-danger flex-fill favorite-toggle';
                        button.innerHTML = '<i class="fas fa-heart-broken me-2"></i>Remove from Favorites';
                        button.setAttribute('data-is-favorited', '1');
                    } else {
                        button.className = 'btn btn-outline-secondary flex-fill favorite-toggle';
                        button.innerHTML = '<i class="fas fa-heart me-2"></i>Save';
                        button.setAttribute('data-is-favorited', '0');
                    }

                    // Show success message
                    showToast(data.message, 'success');
                } else {
                    // Show error message
                    showToast(data.message, 'error');
                    // Reset button
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
                // Reset button
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    });

    // Inquiry form submission
    document.getElementById('submit-inquiry').addEventListener('click', function() {
        const form = document.getElementById('inquiry-form');
        const formData = new FormData(form);
        const button = this;

        // Validate form
        const subject = form.querySelector('[name="subject"]').value.trim();
        const message = form.querySelector('[name="message"]').value.trim();

        if (!subject || !message) {
            showToast('Please fill in all required fields.', 'error');
            return;
        }

        // Show loading state
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        button.disabled = true;

        fetch('<?php echo BASE_URL; ?>inquiry/submit', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('inquiryModal'));
                modal.hide();

                // Reset form
                form.reset();

                // Show success message
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });

    // Toast notification function
    function showToast(message, type) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-body">
                ${message}
            </div>
        `;

        // Add to page
        document.body.appendChild(toast);

        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Hide toast after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 4000);
    }
});
</script>

<style>
/* Toast notifications */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    max-width: 500px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.toast.show {
    opacity: 1;
    transform: translateX(0);
}

.toast-success {
    background: #d4edda;
    border-left: 4px solid #28a745;
    color: #155724;
}

.toast-error {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.toast-body {
    padding: 1rem;
}

@media (max-width: 768px) {
    .toast {
        right: 10px;
        left: 10px;
        min-width: auto;
    }
}
</style>
