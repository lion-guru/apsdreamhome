<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        .property-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .property-image {
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .stats-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 60px 0;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Find Your Dream Home</h1>
                    <p class="lead mb-4">Discover the perfect property that matches your lifestyle and budget from our extensive collection of premium real estate.</p>
                    <div class="d-flex gap-3">
                        <a href="/properties" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-search me-2"></i>Browse Properties
                        </a>
                        <a href="/about" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-info-circle me-2"></i>Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                         alt="Beautiful Home" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">Featured Properties</h2>
                <p class="lead text-muted">Handpicked properties that offer exceptional value and quality</p>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($data['properties'])): ?>
                <?php foreach ($data['properties'] as $property): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card property-card h-100 shadow-sm">
                            <img src="<?php echo htmlspecialchars($property['image_url']); ?>"
                                 class="property-image" alt="<?php echo htmlspecialchars($property['title']); ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <span class="badge bg-success">Featured</span>
                                </div>
                                <p class="card-text text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($property['location']); ?>
                                </p>
                                <p class="card-text mb-3">
                                    <?php echo htmlspecialchars(substr($property['description'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="h5 text-primary mb-0">$<?php echo number_format($property['price']); ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-bed me-1"></i><?php echo $property['bedrooms']; ?> beds
                                        <i class="fas fa-bath ms-2 me-1"></i><?php echo $property['bathrooms']; ?> baths
                                    </div>
                                </div>
                                <a href="/properties/<?php echo $property['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No featured properties available at the moment. Check back soon for new listings!
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="row text-center mt-4">
            <div class="col-12">
                <a href="/properties" class="btn btn-outline-primary btn-lg px-5">
                    <i class="fas fa-th-large me-2"></i>View All Properties
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="stats-number">10+</div>
                    <h5>Years Experience</h5>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stats-number">5k+</div>
                    <h5>Happy Customers</h5>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stats-number">500+</div>
                    <h5>Properties Sold</h5>
                </div>
            </div>
        </div>
    </section>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
