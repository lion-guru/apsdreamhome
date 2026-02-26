<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Find Your Dream Property</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #0f2b66 0%, #1b5fd0 50%, #0f2b66 100%);
            color: white;
            padding: 80px 0;
        }
        .property-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .price-tag {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .feature-icon {
            font-size: 3rem;
            color: #1b5fd0;
            margin-bottom: 1rem;
        }
        .mvc-badge {
            background: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-home me-2"></i>APS Dream Home
                <span class="mvc-badge">MVC</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>properties">Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>projects">Projects</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary text-white ms-2" href="<?php echo BASE_URL; ?>admin">Admin</a></li>
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
                    <p class="lead mb-4">Trusted Real Estate Partner in Gorakhpur, Lucknow & across Uttar Pradesh</p>
                    <form action="<?php echo BASE_URL; ?>properties" method="GET" class="row g-2 bg-white p-3 rounded-3 shadow-lg">
                        <div class="col-md-4">
                            <select name="type" class="form-select">
                                <option value="">Property Type</option>
                                <option value="apartment">Apartments</option>
                                <option value="villa">Villas</option>
                                <option value="commercial">Commercial</option>
                                <option value="plot">Plots / Land</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="location" class="form-control" placeholder="City or Area">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-lg w-100">Search</button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-home" style="font-size: 8rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Featured Properties</h2>
            <div class="row">
                <?php foreach ($properties as $property): ?>
                <div class="col-lg-4 mb-4">
                    <div class="card property-card h-100">
                        <img src="https://via.placeholder.com/400x250/1b5fd0/ffffff?text=<?php echo urlencode($property['title']); ?>" class="card-img-top" alt="<?php echo $property['title']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $property['title']; ?></h5>
                            <p class="card-text"><?php echo $property['description']; ?></p>
                            <div class="mb-2">
                                <span class="price-tag">₹<?php echo number_format($property['price']); ?></span>
                            </div>
                            <div class="text-muted small">
                                <?php if ($property['bedrooms']): ?>
                                    <i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> BD |
                                <?php endif; ?>
                                <i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> BA |
                                <i class="fas fa-ruler-combined"></i> <?php echo $property['area']; ?> sqft
                            </div>
                            <div class="mt-2">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $property['city']; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="<?php echo BASE_URL; ?>properties/<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- MVC Architecture Info -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">MVC Architecture</h2>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h4>Model-View-Controller</h4>
                    <p>Proper MVC structure with controllers and views for maintainable code</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h4>Database Integration</h4>
                    <p>Ready for database connectivity with Property and User models</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h4>Extensible</h4>
                    <p>Easy to extend with new controllers and features</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>APS Dream Home</h5>
                    <p>Your trusted real estate partner in Uttar Pradesh</p>
                </div>
                <div class="col-md-6">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-phone"></i> +91-XXXXXXXXXX</p>
                    <p><i class="fas fa-envelope"></i> info@apsdreamhome.com</p>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center">
                <p>&copy; 2026 APS Dream Home. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
