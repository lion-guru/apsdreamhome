<?php
/**
 * Properties - APS Dream Home
 * Enhanced properties page with search and filtering
 */

require_once 'includes/config.php';
require_once 'includes/enhanced_universal_template.php';

// Create template instance
$template = new EnhancedUniversalTemplate();
$template->setTitle('Properties - APS Dream Homes | Build Your Network')
          ->setDescription('Browse premium properties in Gorakhpur, Lucknow & UP. Earn 7% commissions through our MLM network!')
          ->addCSS('assets/css/property-listing.css')
          ->addJS('assets/js/property-filters.js');

// Render header
$template->renderHeader();
?>

<!-- Hero Section -->
<section class="hero-properties">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Find Your
                        <span class="text-warning">Perfect Property</span>
                    </h1>
                    <p class="hero-subtitle">
                        Discover amazing properties in Gorakhpur, Lucknow and surrounding areas.
                        From luxury apartments to spacious villas, we have something for everyone.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MLM Opportunity Banner -->
<section class="mlm-property-banner">
    <div class="container">
        <h2 class="mlm-banner-title">🎯 Earn 7% Commission on Property Sales!</h2>
        <p class="mlm-banner-subtitle">Join our MLM network and build your real estate business</p>
        <div class="mlm-banner-actions">
            <a href="register_mlm.php" class="btn btn-light btn-lg">🚀 Join MLM Network</a>
            <a href="mlm-opportunity.php" class="btn btn-outline-light btn-lg">Learn More</a>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="search-card">
                    <h3 class="mb-4">Search Properties</h3>
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Property Type</label>
                            <select class="form-select" name="type">
                                <option value="">All Types</option>
                                <option value="apartment">Apartment</option>
                                <option value="villa">Villa</option>
                                <option value="plot">Plot</option>
                                <option value="house">House</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Location</label>
                            <select class="form-select" name="location">
                                <option value="">All Locations</option>
                                <option value="gorakhpur">Gorakhpur</option>
                                <option value="lucknow">Lucknow</option>
                                <option value="kanpur">Kanpur</option>
                                <option value="varanasi">Varanasi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Budget Range</label>
                            <select class="form-select" name="budget">
                                <option value="">Any Budget</option>
                                <option value="0-25lakh">Below 25 Lakhs</option>
                                <option value="25-50lakh">25-50 Lakhs</option>
                                <option value="50-1cr">50 Lakhs - 1 Crore</option>
                                <option value="1cr+">Above 1 Crore</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bedrooms</label>
                            <select class="form-select" name="bedrooms">
                                <option value="">Any</option>
                                <option value="1">1 BHK</option>
                                <option value="2">2 BHK</option>
                                <option value="3">3 BHK</option>
                                <option value="4">4+ BHK</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Search Properties
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Properties Grid -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Featured Properties</h2>
            <p class="lead text-muted">Handpicked properties for you</p>
        </div>
        
        <div class="row g-4">
            <?php
            // Sample properties data
            $properties = [
                [
                    'id' => 1,
                    'title' => 'Luxury Apartment in Gorakhpur',
                    'location' => 'Civil Lines, Gorakhpur',
                    'price' => 2500000,
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area_sqft' => 1200,
                    'type' => 'apartment',
                    'image' => 'https://via.placeholder.com/400x300/667eea/ffffff?text=Luxury+Apartment'
                ],
                [
                    'id' => 2,
                    'title' => 'Modern Villa in Lucknow',
                    'location' => 'Gomti Nagar, Lucknow',
                    'price' => 4500000,
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area_sqft' => 2000,
                    'type' => 'villa',
                    'image' => 'https://via.placeholder.com/400x300/28a745/ffffff?text=Modern+Villa'
                ],
                [
                    'id' => 3,
                    'title' => 'Spacious Plot in Gorakhpur',
                    'location' => 'Rustampur, Gorakhpur',
                    'price' => 1800000,
                    'bedrooms' => 0,
                    'bathrooms' => 0,
                    'area_sqft' => 1500,
                    'type' => 'plot',
                    'image' => 'https://via.placeholder.com/400x300/ffc107/000000?text=Spacious+Plot'
                ],
                [
                    'id' => 4,
                    'title' => '3 BHK Apartment in Kanpur',
                    'location' => 'Kakadeo, Kanpur',
                    'price' => 3200000,
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area_sqft' => 1350,
                    'type' => 'apartment',
                    'image' => 'https://via.placeholder.com/400x300/dc3545/ffffff?text=3+BHK+Apartment'
                ],
                [
                    'id' => 5,
                    'title' => 'Independent House in Varanasi',
                    'location' => 'Sigra, Varanasi',
                    'price' => 3800000,
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area_sqft' => 1800,
                    'type' => 'house',
                    'image' => 'https://via.placeholder.com/400x300/6f42c1/ffffff?text=Independent+House'
                ],
                [
                    'id' => 6,
                    'title' => '2 BHK Flat in Lucknow',
                    'location' => 'Alambagh, Lucknow',
                    'price' => 2800000,
                    'bedrooms' => 2,
                    'bathrooms' => 2,
                    'area_sqft' => 950,
                    'type' => 'apartment',
                    'image' => 'https://via.placeholder.com/400x300/20c997/ffffff?text=2+BHK+Flat'
                ]
            ];
            
            foreach ($properties as $property):
            ?>
                <div class="col-lg-4 col-md-6">
                    <div class="property-card">
                        <div class="property-image">
                            <img src="<?php echo $property['image']; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                            <div class="property-badge">
                                <?php echo ucfirst($property['type']); ?>
                            </div>
                        </div>
                        <div class="property-content">
                            <div class="property-price">₹<?php echo number_format($property['price']); ?></div>
                            <h5 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <div class="property-features">
                                <?php if ($property['bedrooms'] > 0): ?>
                                    <div class="feature-item">
                                        <i class="fas fa-bed"></i>
                                        <span><?php echo $property['bedrooms']; ?> Beds</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($property['bathrooms'] > 0): ?>
                                    <div class="feature-item">
                                        <i class="fas fa-bath"></i>
                                        <span><?php echo $property['bathrooms']; ?> Baths</span>
                                    </div>
                                <?php endif; ?>
                                <div class="feature-item">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span><?php echo number_format($property['area_sqft']); ?> sqft</span>
                                </div>
                            </div>
                            <div class="property-actions">
                                <a href="property_details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-heart me-1"></i>Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <nav aria-label="Property pagination">
                <ul class="pagination">
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

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-3">Can't find what you're looking for?</h2>
                <p class="lead mb-4">Our team is here to help you find the perfect property. Contact us for personalized assistance.</p>
                <a href="contact.php" class="btn btn-warning btn-lg">
                    <i class="fas fa-phone me-2"></i>Contact Our Team
                </a>
            </div>
            <div class="col-lg-4">
                <div class="text-center">
                    <i class="fas fa-headset" style="font-size: 6rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Properties Page Styles */
.hero-properties {
    background: linear-gradient(135deg, rgba(44, 62, 80, 0.9), rgba(52, 152, 219, 0.9)), url('assets/images/properties-hero.jpg');
    background-size: cover;
    background-position: center;
    color: white;
    padding: 8rem 0 4rem;
    text-align: center;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.hero-subtitle {
    font-size: 1.3rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.search-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.property-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.property-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.property-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.property-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.property-card:hover .property-image img {
    transform: scale(1.1);
}

.property-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: var(--bs-primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
}

.property-content {
    padding: 1.5rem;
}

.property-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--bs-primary);
    margin-bottom: 0.5rem;
}

.property-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--bs-dark);
}

.property-location {
    color: var(--bs-secondary);
    margin-bottom: 1rem;
}

.property-features {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: var(--bs-secondary);
}

.property-actions {
    display: flex;
    gap: 0.5rem;
}

.cta-section {
    background: linear-gradient(135deg, var(--bs-warning), var(--bs-primary));
    color: white;
    text-align: center;
}

/* MLM Commission Badge */
.commission-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    z-index: 10;
    box-shadow: 0 2px 10px rgba(40, 167, 69, 0.3);
}

.property-card {
    position: relative;
}

/* MLM Hero Banner */
.mlm-property-banner {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.9), rgba(32, 201, 151, 0.9));
    color: white;
    padding: 2rem 0;
    text-align: center;
    margin-bottom: 2rem;
}

.mlm-banner-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.mlm-banner-subtitle {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    opacity: 0.9;
}

.mlm-banner-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .property-features {
        flex-wrap: wrap;
    }
    
    .property-actions {
        flex-direction: column;
    }
}
</style>

<?php
// Render footer
$template->renderFooter();
?>
