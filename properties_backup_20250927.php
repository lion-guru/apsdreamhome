<?php
/**
 * APS Dream Home - Simple Properties Page
 * Clean and easy to understand
 */

// Start session
session_start();

// Include database connection
require_once 'includes/db_connection.php';

// Get database connection
try {
    $conn = getDbConnection();

    // Get properties
    $properties = [];
    if ($conn) {
        $query = "
            SELECT p.id, p.title, p.address, p.price, p.bedrooms, p.bathrooms, p.area, p.status, p.description,
                   u.first_name, u.last_name,
                   pt.name as property_type,
                   (SELECT pi.image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) as main_image
            FROM properties p
            LEFT JOIN users u ON p.agent_id = u.id
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            WHERE p.status = 'available'
            ORDER BY p.created_at DESC
        ";
        $result = $conn->query($query);
        if ($result) {
            $properties = $result->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // If no properties, add some sample data
    if (empty($properties)) {
        $properties = [
            [
                'id' => 1,
                'title' => 'Beautiful 3BHK Apartment',
                'address' => 'Gorakhpur, Uttar Pradesh',
                'price' => 4500000,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area' => 1200,
                'status' => 'available',
                'description' => 'Modern apartment with excellent amenities',
                'first_name' => 'Rajesh',
                'last_name' => 'Kumar',
                'property_type' => 'Apartment',
                'main_image' => 'https://via.placeholder.com/400x300?text=Property+Image'
            ],
            [
                'id' => 2,
                'title' => 'Spacious Villa',
                'address' => 'Lucknow, Uttar Pradesh',
                'price' => 8500000,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 2000,
                'status' => 'available',
                'description' => 'Luxurious villa with garden',
                'first_name' => 'Priya',
                'last_name' => 'Sharma',
                'property_type' => 'Villa',
                'main_image' => 'https://via.placeholder.com/400x300?text=Villa+Image'
            ]
        ];
    }

} catch (Exception $e) {
    $properties = [];
    $error_message = "Sorry, we're experiencing technical difficulties. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - APS Dream Home</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .property-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .property-card:hover {
            transform: translateY(-5px);
        }

        .property-image {
            height: 250px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 25px;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .search-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }

        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .price-range {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .property-meta {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-home me-2"></i>APS Dream Home
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="properties.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>

                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-outline-light">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-primary text-white py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-3">Find Your Perfect Property</h1>
                    <p class="lead">Browse through our extensive collection of properties</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="mb-0"><strong><?php echo count($properties); ?></strong> Properties Available</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Search and Filter Section -->
    <section class="py-4">
        <div class="container">
            <div class="search-section">
                <form action="properties.php" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Property Type</label>
                            <select class="form-select" name="type">
                                <option value="">All Types</option>
                                <option value="apartment">Apartment</option>
                                <option value="villa">Villa</option>
                                <option value="house">House</option>
                                <option value="plot">Plot</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Location</label>
                            <select class="form-select" name="location">
                                <option value="">All Locations</option>
                                <option value="Gorakhpur">Gorakhpur</option>
                                <option value="Lucknow">Lucknow</option>
                                <option value="Mumbai">Mumbai</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Min Price</label>
                            <input type="number" class="form-control" name="min_price" placeholder="₹0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Price</label>
                            <input type="number" class="form-control" name="max_price" placeholder="No limit">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bedrooms</label>
                            <select class="form-select" name="bedrooms">
                                <option value="">Any</option>
                                <option value="1">1 BHK</option>
                                <option value="2">2 BHK</option>
                                <option value="3">3 BHK</option>
                                <option value="4">4+ BHK</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Search Properties
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Properties Grid -->
    <section class="py-4">
        <div class="container">
            <?php if (empty($properties)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No properties found matching your criteria. Please try different filters.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($properties as $property): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card property-card h-100">
                                <div class="position-relative">
                                    <img src="<?php echo htmlspecialchars($property['main_image'] ?? 'https://via.placeholder.com/400x300?text=Property'); ?>"
                                         alt="<?php echo htmlspecialchars($property['title'] ?? 'Property'); ?>"
                                         class="card-img-top property-image">

                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($property['property_type'] ?? 'Property'); ?></span>
                                    </div>

                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-success">₹<?php echo number_format($property['price'] ?? 0); ?></span>
                                    </div>

                                    <button class="btn btn-light btn-sm position-absolute bottom-0 end-0 m-2" onclick="toggleFavorite(<?php echo $property['id'] ?? 0; ?>)">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="property-details.php?id=<?php echo $property['id'] ?? 0; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($property['title'] ?? 'Untitled Property'); ?>
                                        </a>
                                    </h5>

                                    <p class="card-text text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($property['address'] ?? 'Location not specified'); ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="property-meta"><i class="fas fa-bed me-1"></i> <?php echo $property['bedrooms'] ?? 0; ?> Beds</span>
                                        <span class="property-meta"><i class="fas fa-bath me-1"></i> <?php echo $property['bathrooms'] ?? 0; ?> Baths</span>
                                        <span class="property-meta"><i class="fas fa-ruler-combined me-1"></i> <?php echo number_format($property['area'] ?? 0); ?> sq.ft</span>
                                    </div>

                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars(substr($property['description'] ?? 'No description available', 0, 100)); ?>...
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars(($property['first_name'] ?? '') . ' ' . ($property['last_name'] ?? '')); ?>
                                        </small>
                                        <span class="badge bg-<?php echo ($property['status'] ?? '') === 'available' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($property['status'] ?? 'Unknown'); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent border-0">
                                    <div class="d-grid gap-2">
                                        <a href="property-details.php?id=<?php echo $property['id'] ?? 0; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i> View Details
                                        </a>
                                        <button class="btn btn-outline-primary btn-sm" onclick="scheduleVisit(<?php echo $property['id'] ?? 0; ?>)">
                                            <i class="fas fa-calendar me-1"></i> Schedule Visit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="text-white mb-3">APS Dream Home</h5>
                    <p class="text-white-50">Your trusted partner in real estate solutions.</p>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white-50">Home</a></li>
                        <li class="mb-2"><a href="properties.php" class="text-white-50">Properties</a></li>
                        <li class="mb-2"><a href="about.php" class="text-white-50">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white-50">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-white mb-3">Contact Info</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Gorakhpur, Uttar Pradesh, India</li>
                        <li class="mb-2"><i class="fas fa-phone-alt me-2"></i> +91-9000000001</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@apsdreamhome.com</li>
                        <li><i class="fas fa-clock me-2"></i> Mon-Sat: 9:00 AM - 8:00 PM</li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-white mb-3">Newsletter</h5>
                    <p class="text-white-50">Subscribe for latest property updates</p>
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="my-4 bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-white-50">&copy; 2025 APS Dream Home. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white-50 me-3">Privacy Policy</a>
                    <a href="#" class="text-white-50">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Property favorites and scheduling
        function toggleFavorite(propertyId) {
            // Add to favorites functionality
            console.log('Toggling favorite for property:', propertyId);
            const button = event.target.closest('button');
            const icon = button.querySelector('i');

            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.classList.add('btn-danger');
                button.classList.remove('btn-light');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.classList.add('btn-light');
                button.classList.remove('btn-danger');
            }
        }

        function scheduleVisit(propertyId) {
            // Schedule visit functionality
            console.log('Scheduling visit for property:', propertyId);
            alert('Visit scheduling feature coming soon! Contact us directly for now.');
        }
    </script>
</body>
</html>
