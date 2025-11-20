<?php
// Simple test page to verify UI/UX setup
require_once 'development_mode.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - UI/UX Test</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --accent-color: #f59e0b;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), #1d4ed8);
            color: white;
            padding: 100px 0;
        }
        
        .property-card {
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-home me-2"></i>APS Dream Home
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light ms-2 px-3" href="#">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Find Your Dream Home</h1>
            <p class="lead mb-4">Discover premium properties with modern UI/UX design</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="#" class="btn btn-light btn-lg px-4">Browse Properties</a>
                <a href="#" class="btn btn-outline-light btn-lg px-4">Contact Us</a>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Featured Properties</h2>
                <p class="lead text-muted">Modern, user-friendly property showcase</p>
            </div>
            
            <div class="row">
                <?php for($i = 1; $i <= 6; $i++): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="property-card card h-100 shadow">
                        <img src="https://via.placeholder.com/400x250/007bff/ffffff?text=Property+<?php echo $i; ?>" class="card-img-top" alt="Property <?php echo $i; ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Beautiful Property <?php echo $i; ?></h5>
                            <p class="card-text text-muted flex-grow-1">Modern property with excellent features and prime location.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h5 text-primary mb-0">₹<?php echo number_format(rand(2500000, 8000000)); ?></span>
                                <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Location <?php echo $i; ?></small>
                            </div>
                            <button class="btn btn-primary w-100">View Details</button>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Services</h2>
                <p class="lead text-muted">User-friendly real estate solutions</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 text-center">
                    <div class="card h-100 p-4">
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <h4>Property Search</h4>
                        <p>Find the perfect property with our intuitive search tools.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 text-center">
                    <div class="card h-100 p-4">
                        <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                        <h4>Property Sales</h4>
                        <p>Sell your property quickly with our modern marketing approach.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 text-center">
                    <div class="card h-100 p-4">
                        <i class="fas fa-calculator fa-3x text-primary mb-3"></i>
                        <h4>Financial Advice</h4>
                        <p>Get expert financial guidance for your property decisions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <h5>APS Dream Home</h5>
                    <p>Your trusted partner in finding the perfect home.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50">Properties</a></li>
                        <li><a href="#" class="text-white-50">Services</a></li>
                        <li><a href="#" class="text-white-50">About Us</a></li>
                        <li><a href="#" class="text-white-50">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i>123 Real Estate Ave, City</p>
                    <p><i class="fas fa-phone me-2"></i>+91 12345 67890</p>
                    <p><i class="fas fa-envelope me-2"></i>info@apsdreamhome.com</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2024 APS Dream Home. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Simple JavaScript for interactivity
        document.addEventListener('DOMContentLoaded', function() {
            console.log('APS Dream Home UI/UX Test Loaded Successfully!');
            
            // Add smooth scrolling
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Add click handlers for property cards
            document.querySelectorAll('.property-card button').forEach(button => {
                button.addEventListener('click', function() {
                    alert('Property details page would open here!');
                });
            });
        });
    </script>
</body>
</html>