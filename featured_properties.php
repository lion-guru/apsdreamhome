<?php
/**
 * Featured Properties Page - APS Dream Homes
 * Display featured property listings
 */

require_once 'core/functions.php';
require_once 'includes/db_connection.php';

try {
    $pdo = getMysqliConnection();

    // Get featured properties
    $featuredQuery = "SELECT * FROM properties WHERE featured = 1 AND status = 'available' ORDER BY created_at DESC LIMIT 12";
    $featuredStmt = $pdo->query($featuredQuery);
    $featuredProperties = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get property types for filter
    $typesQuery = "SELECT DISTINCT property_type FROM properties WHERE featured = 1 AND status = 'available'";
    $typesStmt = $pdo->query($typesQuery);
    $propertyTypes = $typesStmt->fetchAll(PDO::FETCH_COLUMN);

    // Get locations for filter
    $locationsQuery = "SELECT DISTINCT location FROM properties WHERE featured = 1 AND status = 'available'";
    $locationsStmt = $pdo->query($locationsQuery);
    $locations = $locationsStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    error_log('Featured properties page database error: ' . $e->getMessage());
    $featuredProperties = [];
    $propertyTypes = [];
    $locations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Include site settings
    require_once 'includes/site_settings.php';
    ?>
    <title><?php echo getSiteSetting('site_title', 'APS Dream Homes'); ?> - Featured Properties</title>
    <meta name="description" content="Discover our handpicked selection of featured properties at APS Dream Homes. Premium real estate listings in prime locations.">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        .featured-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }

        .property-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
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
            transform: scale(1.05);
        }

        .featured-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        .property-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 20px;
        }

        .property-info {
            padding: 20px;
        }

        .property-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .property-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .property-location {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .property-features {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: #666;
        }

        .property-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 30px 0;
            margin-bottom: 40px;
        }

        .filter-btn {
            border-radius: 25px;
            padding: 10px 20px;
            margin: 5px;
            transition: all 0.3s ease;
        }

        .breadcrumb {
            background: #f8f9fa;
            border-radius: 0;
        }

        .empty-state {
            padding: 60px 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="featured-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Featured Properties</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                        Discover our handpicked selection of premium properties in the most sought-after locations.
                    </p>
                    <div class="row g-4 mt-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-star fa-3x text-warning mb-3"></i>
                                <h4 class="h5">Premium Selection</h4>
                                <p class="small mb-0">Carefully curated properties that meet our highest standards</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-map-marker-alt fa-3x text-info mb-3"></i>
                                <h4 class="h5">Prime Locations</h4>
                                <p class="small mb-0">Properties in the most desirable neighborhoods and areas</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-thumbs-up fa-3x text-success mb-3"></i>
                                <h4 class="h5">Verified Quality</h4>
                                <p class="small mb-0">All featured properties are thoroughly inspected and verified</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav class="bg-light border-bottom py-2" aria-label="breadcrumb">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="properties.php">Properties</a></li>
                <li class="breadcrumb-item active" aria-current="page">Featured Properties</li>
            </ol>
        </div>
    </nav>

    <!-- Featured Properties Content -->
    <main class="py-5">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="row">
                    <div class="col-12">
                        <div class="text-center mb-4">
                            <h3 class="h4 mb-3">Filter Featured Properties</h3>
                        </div>
                        <div class="d-flex justify-content-center flex-wrap gap-2">
                            <a href="featured_properties.php" class="btn btn-primary filter-btn">All Featured</a>
                            <?php if (!empty($propertyTypes)): ?>
                                <?php foreach ($propertyTypes as $type): ?>
                                <a href="featured_properties.php?type=<?php echo urlencode($type); ?>"
                                   class="btn btn-outline-primary filter-btn">
                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $type))); ?>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Properties Grid -->
            <?php if (!empty($featuredProperties)): ?>
            <div class="row g-4">
                <?php foreach ($featuredProperties as $property): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="property-card">
                        <div class="property-image">
                            <div class="featured-badge">
                                <i class="fas fa-star me-1"></i>Featured
                            </div>
                            <img src="<?php echo htmlspecialchars($property['image_url'] ?? 'assets/images/property-placeholder.jpg'); ?>"
                                 alt="<?php echo htmlspecialchars($property['title']); ?>">
                            <div class="property-overlay">
                                <div class="d-flex justify-content-between align-items-end">
                                    <div>
                                        <span class="badge bg-light text-dark mb-2"><?php echo htmlspecialchars($property['property_type']); ?></span>
                                        <h5 class="mb-0"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="property-info">
                            <div class="property-price">
                                ₹<?php echo htmlspecialchars(number_format($property['price'])); ?>
                            </div>
                            <h6 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h6>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <div class="property-features">
                                <?php if (!empty($property['bedrooms'])): ?>
                                <div class="feature-item">
                                    <i class="fas fa-bed text-muted"></i>
                                    <span><?php echo htmlspecialchars($property['bedrooms']); ?> Bed</span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($property['bathrooms'])): ?>
                                <div class="feature-item">
                                    <i class="fas fa-bath text-muted"></i>
                                    <span><?php echo htmlspecialchars($property['bathrooms']); ?> Bath</span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($property['area'])): ?>
                                <div class="feature-item">
                                    <i class="fas fa-ruler-combined text-muted"></i>
                                    <span><?php echo htmlspecialchars($property['area']); ?> sq.ft</span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="property-actions">
                                <a href="property-details.php?id=<?php echo $property['id']; ?>"
                                   class="btn btn-primary btn-sm flex-fill">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                <a href="contact.php?property=<?php echo $property['id']; ?>"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-phone"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-home fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">No Featured Properties Available</h3>
                    <p class="text-muted mb-4">
                        We're currently updating our featured properties collection. Please check back soon for new premium listings.
                    </p>
                    <a href="properties.php" class="btn btn-primary">Browse All Properties</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Call to Action -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="text-center p-5 bg-light rounded-3">
                        <h3 class="mb-3">Don't See What You're Looking For?</h3>
                        <p class="text-muted mb-4">Contact our property experts for personalized recommendations and exclusive listings</p>
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <a href="contact.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </a>
                            <a href="properties.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Browse All Properties
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
</body>
</html>
