<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Test Header</title>
</head>
<body>
    <?php
    // Define BASE_URL for testing
    if (!defined('BASE_URL')) {
        define('BASE_URL', '/');
    }
    ?>

    <?php require_once 'app/views/layouts/header_new_fixed.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">APS Dream Home</h1>
                <p class="text-center">Your trusted partner in real estate</p>

                <div class="row mt-5">
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Premium Properties</h5>
                                <p class="card-text">Discover our curated collection of premium properties across Uttar Pradesh.</p>
                                <a href="<?php echo BASE_URL; ?>properties" class="btn btn-primary">View Properties</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Expert Support</h5>
                                <p class="card-text">Our dedicated team provides personalized guidance throughout your property journey.</p>
                                <a href="<?php echo BASE_URL; ?>about" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Get Started</h5>
                                <p class="card-text">Join thousands of satisfied customers who found their perfect property.</p>
                                <a href="<?php echo BASE_URL; ?>contact" class="btn btn-primary">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
