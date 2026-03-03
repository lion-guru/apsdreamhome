<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Find Your Dream Property</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .property-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .property-image {
            height: 200px;
            object-fit: cover;
        }
        .price-tag {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-home"></i> APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/properties">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/admin">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Find Your Dream Home</h1>
                    <p class="lead mb-4">Discover the perfect property from our extensive collection of homes, apartments, and villas across India.</p>
                    <div class="d-flex gap-3">
                        <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-light btn-lg">Browse Properties</a>
                        <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-outline-light btn-lg">Contact Us</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-home" style="font-size: 200px; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Search Properties</h4>
                            <form class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" placeholder="Location">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select">
                                        <option>Property Type</option>
                                        <option>House</option>
                                        <option>Apartment</option>
                                        <option>Villa</option>
                                        <option>Land</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" placeholder="Min Price">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" placeholder="Max Price">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select">
                                        <option>Bedrooms</option>
                                        <option>1</option>
                                        <option>2</option>
                                        <option>3</option>
                                        <option>4+</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <h2 class="text-center mb-4">Featured Properties</h2>
                </div>
            </div>
            <div class="row">
                <?php if (isset($featuredProperties) && !empty($featuredProperties)): ?>
                    <?php foreach ($featuredProperties as $property): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card property-card">
                                <img src="<?php echo BASE_URL; ?>/<?php echo $property['image']; ?>" class="card-img-top property-image" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <p class="card-text">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['location']); ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price-tag">₹<?php echo number_format($property['price']); ?></span>
                                        <div>
                                            <i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?>
                                            <i class="fas fa-bath ms-2"></i> <?php echo $property['bathrooms']; ?>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="<?php echo BASE_URL; ?>/properties/<?php echo $property['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-lg-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No featured properties available at the moment.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Recent Properties -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <h2 class="text-center mb-4">Recent Properties</h2>
                </div>
            </div>
            <div class="row">
                <?php if (isset($recentProperties) && !empty($recentProperties)): ?>
                    <?php foreach ($recentProperties as $property): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card property-card">
                                <img src="<?php echo BASE_URL; ?>/<?php echo $property['image']; ?>" class="card-img-top property-image" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <p class="card-text">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['location']); ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price-tag">₹<?php echo number_format($property['price']); ?></span>
                                        <div>
                                            <i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?>
                                            <i class="fas fa-bath ms-2"></i> <?php echo $property['bathrooms']; ?>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="<?php echo BASE_URL; ?>/properties/<?php echo $property['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-lg-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No recent properties available at the moment.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <h2 class="text-center mb-4">Our Services</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="text-center">
                        <i class="fas fa-home fa-3x mb-3 text-primary"></i>
                        <h4>Property Sales</h4>
                        <p>We help you find and purchase your dream property with our extensive network and expertise.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="text-center">
                        <i class="fas fa-chart-line fa-3x mb-3 text-success"></i>
                        <h4>Property Investment</h4>
                        <p>Get expert advice on property investment opportunities and maximize your returns.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="text-center">
                        <i class="fas fa-shield-alt fa-3x mb-3 text-warning"></i>
                        <h4>Legal Assistance</h4>
                        <p>Our team provides complete legal support for all your property transactions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h5>APS Dream Home</h5>
                    <p>Your trusted partner in finding the perfect property.</p>
                </div>
                <div class="col-lg-6">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-phone"></i> +91 98765 43210</p>
                    <p><i class="fas fa-envelope"></i> info@apsdreamhome.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Mumbai, India</p>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p>&copy; 2026 APS Dream Home. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
