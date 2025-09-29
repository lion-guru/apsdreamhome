<?php
session_start();
$user_logged_in = isset($_SESSION["user_id"]);
$user_name = $user_logged_in ? $_SESSION["user_name"] : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Find Your Perfect Property</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: "Poppins", sans-serif; margin: 0; padding: 0; }
        .hero { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: white; padding: 80px 0; text-align: center; }
        .hero h1 { font-size: 3rem; font-weight: 700; margin-bottom: 20px; }
        .hero p { font-size: 1.2rem; margin-bottom: 40px; opacity: 0.9; }
        .search-box { max-width: 600px; margin: 0 auto; padding: 30px; background: rgba(255,255,255,0.1); border-radius: 15px; }
        .search-input { height: 50px; border: none; border-radius: 25px; padding: 0 20px; font-size: 16px; }
        .search-btn { height: 50px; border-radius: 25px; background: #f59e0b; border: none; color: white; font-weight: 600; padding: 0 30px; }
        .navbar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 15px 0; }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; color: #1e3a8a; }
        .nav-link { color: #333; font-weight: 500; }
        .nav-link:hover { color: #1e3a8a; }
        .section { padding: 60px 0; }
        .property-card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .property-card:hover { transform: translateY(-5px); }
        .property-img { height: 200px; object-fit: cover; }
        .footer { background: #1f2937; color: white; text-align: center; padding: 40px 0; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-home me-2"></i>APS Dream Home</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="properties.php">Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <?php if($user_logged_in): ?>
                        <li class="nav-item"><a class="nav-link" href="profile.php">Hi, <?php echo htmlspecialchars($user_name); ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Find Your Dream Home</h1>
            <p>Discover amazing properties for sale and rent across India'\''s top cities</p>
            <div class="search-box">
                <form class="d-flex" action="property-listings.php" method="get">
                    <input type="text" name="search" class="form-control search-input me-3" placeholder="Enter location, property type...">
                    <button type="submit" class="btn search-btn">Search</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="container section">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <a href="property-listings.php?type=buy" class="text-decoration-none">
                    <div class="property-card p-4">
                        <i class="fas fa-home fa-3x text-primary mb-3"></i>
                        <h5>Buy Property</h5>
                        <p class="text-muted">Find homes for sale</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-4">
                <a href="property-listings.php?type=rent" class="text-decoration-none">
                    <div class="property-card p-4">
                        <i class="fas fa-key fa-3x text-success mb-3"></i>
                        <h5>Rent Property</h5>
                        <p class="text-muted">Find rental homes</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-4">
                <a href="post-property.php" class="text-decoration-none">
                    <div class="property-card p-4">
                        <i class="fas fa-plus-circle fa-3x text-warning mb-3"></i>
                        <h5>Post Property</h5>
                        <p class="text-muted">List your property</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-4">
                <a href="contact.php" class="text-decoration-none">
                    <div class="property-card p-4">
                        <i class="fas fa-headset fa-3x text-info mb-3"></i>
                        <h5>Support</h5>
                        <p class="text-muted">Get help & support</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section class="bg-light section">
        <div class="container">
            <h2 class="text-center mb-5">Featured Properties</h2>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="property-card">
                        <img src="https://via.placeholder.com/300x200/1e3a8a/ffffff?text=Property+1" class="property-img w-100" alt="Property">
                        <div class="p-3">
                            <h6>Luxury Apartment</h6>
                            <p class="text-muted">Mumbai, Maharashtra</p>
                            <p class="text-primary fw-bold">?50,00,000</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="property-card">
                        <img src="https://via.placeholder.com/300x200/1e40af/ffffff?text=Property+2" class="property-img w-100" alt="Property">
                        <div class="p-3">
                            <h6>Modern Villa</h6>
                            <p class="text-muted">Delhi, NCR</p>
                            <p class="text-primary fw-bold">?1,20,00,000</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="property-card">
                        <img src="https://via.placeholder.com/300x200/f59e0b/ffffff?text=Property+3" class="property-img w-100" alt="Property">
                        <div class="p-3">
                            <h6>Cozy Flat</h6>
                            <p class="text-muted">Bangalore, Karnataka</p>
                            <p class="text-primary fw-bold">?35,00,000</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="property-card">
                        <img src="https://via.placeholder.com/300x200/10b981/ffffff?text=Property+4" class="property-img w-100" alt="Property">
                        <div class="p-3">
                            <h6>Spacious House</h6>
                            <p class="text-muted">Pune, Maharashtra</p>
                            <p class="text-primary fw-bold">?75,00,000</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="property-listings.php" class="btn btn-primary btn-lg">View All Properties</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>Why Choose APS Dream Home?</h2>
                    <p class="lead">We are India'\''s most trusted real estate platform, helping millions find their perfect home.</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Verified Properties</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Expert Agents</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> Best Prices</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> 24/7 Support</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="https://via.placeholder.com/500x300/1e3a8a/ffffff?text=About+APS+Dream+Home" class="img-fluid rounded" alt="About Us">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="bg-light section">
        <div class="container text-center">
            <h2>Get In Touch</h2>
            <p class="lead mb-4">Have questions? We'\''re here to help you find your dream home.</p>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <i class="fas fa-phone fa-2x text-primary mb-3"></i>
                    <h5>Call Us</h5>
                    <p>+91 12345 67890</p>
                </div>
                <div class="col-md-4 mb-3">
                    <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                    <h5>Email Us</h5>
                    <p>info@apsdreamhome.com</p>
                </div>
                <div class="col-md-4 mb-3">
                    <i class="fas fa-map-marker-alt fa-2x text-primary mb-3"></i>
                    <h5>Visit Us</h5>
                    <p>Mumbai, Maharashtra, India</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 APS Dream Home. All rights reserved.</p>
            <div class="mt-3">
                <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
