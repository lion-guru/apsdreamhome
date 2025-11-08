<?php
// Simple test homepage without database dependency
$page_title = 'APS Dream Home - Welcome';

// Start output buffering
ob_start();
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-3 fw-bold mb-4">Welcome to APS Dream Home</h1>
                <p class="lead mb-4">Your trusted partner in real estate with premium properties and exceptional service.</p>
                <div class="hero-buttons">
                    <a href="properties" class="btn btn-light btn-lg me-3 mb-2">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                    <a href="about" class="btn btn-outline-light btn-lg mb-2">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="feature-card p-4 rounded shadow-sm h-100">
                    <i class="fas fa-home fa-3x text-primary mb-3"></i>
                    <h4>Premium Properties</h4>
                    <p>Discover a curated selection of premium residential and commercial properties across prime locations.</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-card p-4 rounded shadow-sm h-100">
                    <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                    <h4>Expert Guidance</h4>
                    <p>Our experienced team provides personalized guidance throughout your property journey.</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-card p-4 rounded shadow-sm h-100">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h4>Trusted Service</h4>
                    <p>With years of experience and thousands of satisfied customers, we deliver reliable service you can trust.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// End output buffering and get content
$content = ob_get_clean();

// Simple template without database dependency
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="fas fa-home me-2"></i>APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="properties">Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact">Contact</a></li>
                </ul>
                <div class="d-flex">
                    <a href="login" class="btn btn-outline-light me-2">Login</a>
                    <a href="register" class="btn btn-success">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main style="margin-top: 80px;">
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
