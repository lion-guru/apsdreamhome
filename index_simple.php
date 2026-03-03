<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Real Estate Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .stats-card {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index_simple.php">
                <i class="fas fa-home"></i> APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index_simple.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_simple.php">Admin Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Welcome to APS Dream Home</h1>
            <p class="lead mb-5">Your Complete Real Estate Management Solution</p>
            <a href="admin_simple.php" class="btn btn-light btn-lg me-3">Admin Dashboard</a>
            <a href="#features" class="btn btn-outline-light btn-lg">Learn More</a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Features & Capabilities</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-tachometer-alt fa-3x text-primary mb-3"></i>
                            <h5>Admin Dashboard</h5>
                            <p>Comprehensive admin interface with real-time statistics and management tools.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x text-success mb-3"></i>
                            <h5>User Management</h5>
                            <p>Complete user management system with roles, permissions, and authentication.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-home fa-3x text-warning mb-3"></i>
                            <h5>Property Management</h5>
                            <p>Advanced property listing, management, and search capabilities.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-key fa-3x text-info mb-3"></i>
                            <h5>Security System</h5>
                            <p>Robust security with key management and access control.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-code fa-3x text-secondary mb-3"></i>
                            <h5>MVC Architecture</h5>
                            <p>Modern MVC architecture with clean, maintainable code.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-database fa-3x text-danger mb-3"></i>
                            <h5>Database Integration</h5>
                            <p>Seamless database integration with PDO and ORM support.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Project Status Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h3>Project Status</h3>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" style="width: 95%">95% Complete</div>
                    </div>
                    <p>This project is 95% complete with all major components implemented and working.</p>
                </div>
                <div class="col-md-6">
                    <h3>Technologies Used</h3>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> PHP 8.5.3</li>
                        <li><i class="fas fa-check text-success"></i> Bootstrap 5</li>
                        <li><i class="fas fa-check text-success"></i> MySQL/SQLite Database</li>
                        <li><i class="fas fa-check text-success"></i> MVC Architecture</li>
                        <li><i class="fas fa-check text-success"></i> REST API Ready</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h2>About APS Dream Home</h2>
                    <p class="lead">A comprehensive real estate management system designed to streamline property management, user administration, and business operations.</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5><i class="fas fa-rocket text-primary"></i> Fast & Efficient</h5>
                            <p>Built with modern PHP and optimized for performance.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5><i class="fas fa-shield-alt text-success"></i> Secure & Reliable</h5>
                            <p>Enterprise-grade security with robust authentication.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5><i class="fas fa-cogs text-warning"></i> Easy to Use</h5>
                            <p>Intuitive interface designed for maximum productivity.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2026 APS Dream Home. All rights reserved.</p>
            <p>Real Estate Management System - Built with PHP, Bootstrap, and MySQL</p>
            <p class="mb-0">
                <small>
                    <i class="fas fa-code"></i> Version 1.0.0 | 
                    <i class="fas fa-server"></i> PHP <?php echo PHP_VERSION; ?> | 
                    <i class="fas fa-check-circle text-success"></i> Production Ready
                </small>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
