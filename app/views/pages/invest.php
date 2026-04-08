<!-- Hero Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-chart-line me-3"></i>Investment Opportunities</h1>
        <p class="lead">Smart investment options with high returns</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h3>Why Invest in Real Estate?</h3>
                <p class="text-muted">Real estate is one of the most stable and profitable investment options. With our expertise, make the right investment decisions.</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-trending-up fa-3x text-success mb-3"></i>
                        <h5>High Returns</h5>
                        <p class="text-muted">Get excellent returns on your investment with properties in growing areas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5>Safe & Secure</h5>
                        <p class="text-muted">All our properties are legally verified with clear documentation</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-hand-holding-usd fa-3x text-warning mb-3"></i>
                        <h5>Easy Financing</h5>
                        <p class="text-muted">Home loan assistance available with attractive interest rates</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12 text-center">
                <h4>Featured Investment Opportunities</h4>
            </div>
            <?php if (!empty($featured_properties)): ?>
                <?php foreach (array_slice($featured_properties, 0, 3) as $project): 
                    $slug = $project['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project['title']));
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="text-muted small"><?php echo htmlspecialchars($project['location']); ?></p>
                            <p class="h4 text-success"><?php echo $project['price']; ?></p>
                            <a href="<?php echo BASE_URL; ?>/projects/<?php echo $slug; ?>" class="btn btn-outline-success">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="tel:+919277121112" class="btn btn-warning btn-lg">
                <i class="fas fa-phone me-2"></i>Call for Investment Advice
            </a>
        </div>
    </div>
</section>
