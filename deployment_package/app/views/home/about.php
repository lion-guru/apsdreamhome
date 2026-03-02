<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($data['title']) ? $data['title'] : 'About Us' ?> - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h1 class="display-5 fw-bold mb-3">About APS Dream Home</h1>
                <p class="lead">We are dedicated to making real estate simple, transparent, and delightful. From budget homes to luxury villas, we help you find a place that truly feels like home.</p>
                <p>With a curated inventory, expert guidance, and end-to-end assistance, we’ve helped thousands of families and investors find the right properties across major cities.</p>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop" class="img-fluid rounded shadow" alt="About APS Dream Home">
            </div>
        </div>

        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="display-6 text-primary mb-2">10+</div>
                        <h5 class="card-title">Years Experience</h5>
                        <p class="card-text text-muted">A decade of trust and excellence in real estate.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="display-6 text-primary mb-2">5k+</div>
                        <h5 class="card-title">Happy Customers</h5>
                        <p class="card-text text-muted">Helping people find homes they love.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="display-6 text-primary mb-2">500+</div>
                        <h5 class="card-title">Verified Listings</h5>
                        <p class="card-text text-muted">Curated properties with verified details and pricing.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="h4 mb-3">Our Mission</h3>
                        <p class="mb-0">To simplify property discovery and transactions using technology, data transparency, and personalized service—so that every customer can make confident real estate decisions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
