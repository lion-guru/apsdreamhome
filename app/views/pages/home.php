<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to APS Dream Home</title>
    <meta name="description" content="Discover premium properties and find your dream home with APS Dream Home - Your trusted real estate partner in UP">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .property-card {
            transition: transform 0.3s ease;
        }
        .property-card:hover {
            transform: translateY(-5px);
        }
        .logo {
            height: 40px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="https://via.placeholder.com/40x40/2c3e50/ffffff?text=APS" alt="APS Dream Home" class="logo me-2">
                <div class="brand-text fw-bold text-primary">APS Dream Home</div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-4" href="/senior-developer/unified">
                            <i class="fas fa-code me-2"></i>Developer Portal
                        </a>
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
                    <h1 class="display-4 fw-bold mb-4">Find Your <span class="text-warning">Dream Home</span></h1>
                    <p class="lead mb-4">Discover premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh with APS Dream Home.</p>
                    <div class="d-flex gap-3">
                        <a href="/properties" class="btn btn-warning btn-lg">Browse Properties</a>
                        <a href="/contact" class="btn btn-outline-light btn-lg">Contact Us</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="http://localhost/apsdreamhome/assets/images/hero/luxury-home-1.jpg" alt="Dream Property" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="card-body">
                            <i class="fas fa-home fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Premium Properties</h5>
                            <p class="card-text">Handpicked selection of luxury homes and commercial spaces</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="card-body">
                            <i class="fas fa-map-marked-alt fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Prime Locations</h5>
                            <p class="card-text">Strategic locations in Gorakhpur, Lucknow, and UP</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="card-body">
                            <i class="fas fa-user-tie fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Expert Guidance</h5>
                            <p class="card-text">Professional assistance throughout your property journey</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body">
                            <h3 class="text-primary"><?php echo $hero_stats['years_experience']; ?>+</h3>
                            <p class="mb-0">Years Experience</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body">
                            <h3 class="text-success"><?php echo $hero_stats['projects_completed']; ?>+</h3>
                            <p class="mb-0">Projects Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body">
                            <h3 class="text-info"><?php echo $hero_stats['happy_customers']; ?>+</h3>
                            <p class="mb-0">Happy Customers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body">
                            <h3 class="text-warning"><?php echo $hero_stats['awards_won']; ?></h3>
                            <p class="mb-0">Awards Won</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Properties Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center display-5 fw-bold mb-5">Featured Properties</h2>
            <div class="row">
                <?php foreach ($featured_properties as $property): ?>
                <div class="col-md-6 mb-4">
                    <div class="card property-card border-0 shadow">
                        <img src="http://localhost/apsdreamhome/assets/images/<?php echo $property['image']; ?>" class="card-img-top" alt="<?php echo $property['title']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $property['title']; ?></h5>
                            <p class="text-muted mb-2"><i class="fas fa-map-marker-alt"></i> <?php echo $property['location']; ?></p>
                            <h4 class="text-primary"><?php echo $property['price']; ?></h4>
                            <span class="badge bg-success"><?php echo $property['status']; ?></span>
                            <div class="mt-3">
                                <a href="/property/<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="/properties" class="btn btn-lg btn-outline-primary">View All Properties</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">About APS Dream Home</h2>
                    <p class="lead">With over <?php echo $hero_stats['years_experience']; ?> years of excellence in real estate, APS Dream Home has been helping families and businesses find their perfect properties across Gorakhpur, Lucknow, and Uttar Pradesh.</p>
                    <p>Our commitment to quality, transparency, and customer satisfaction has made us a trusted name in the real estate industry.</p>
                    <a href="/about" class="btn btn-primary">Learn More</a>
                </div>
                <div class="col-lg-6">
                    <img src="http://localhost/apsdreamhome/assets/images/hero-1.jpg" alt="About APS Dream Home" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Developer Portal Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Developer Portal</h2>
            <p class="lead mb-4">Access our advanced development platform with AI-powered tools, code editor, and system monitoring</p>
            <a href="/senior-developer/unified" class="btn btn-warning btn-lg">
                <i class="fas fa-code me-2"></i>Enter Developer Portal
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://via.placeholder.com/40x40/ffffff/2c3e50?text=APS" alt="APS Dream Home" class="me-2" style="height:40px; border-radius:8px;">
                        <h5 class="mb-0 text-white">APS Dream Home</h5>
                    </div>
                    <p class="text-light">Your trusted real estate partner in Uttar Pradesh</p>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/" class="text-light text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="/about" class="text-light text-decoration-none">About</a></li>
                        <li class="mb-2"><a href="/contact" class="text-light text-decoration-none">Contact</a></li>
                        <li class="mb-2"><a href="/senior-developer/unified" class="text-light text-decoration-none">Developer Portal</a></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p class="mb-0 text-light">&copy; 2026 APS Dream Home. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
