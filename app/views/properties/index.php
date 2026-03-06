<?php
// Properties Index Page - APS Dream Home
?>

<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-4 fw-bold text-center mb-4">Properties</h1>
                <p class="lead text-center">Browse our extensive collection of residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <?php foreach ($properties ?? [] as $property): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card property-card">
                            <?php if ($property['featured']): ?>
                                <div class="featured-badge">Featured</div>
                            <?php endif; ?>
                            
                            <img src="<?php echo $property['image']; ?>" class="card-img-top" alt="<?php echo $property['title']; ?>">
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $property['title']; ?></h5>
                                <p class="text-muted"><?php echo $property['location']; ?></p>
                                <p class="text-primary fw-bold">₹<?php echo number_format($property['price']); ?></p>
                                <p class="small"><?php echo $property['bedrooms']; ?> BHK • <?php echo $property['area']; ?> sq.ft.</p>
                                <p><?php echo $property['description']; ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-<?php echo $property['status'] == 'ready-to-move' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                    <a href="#" class="btn btn-primary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h3 class="card-title">Property Stats</h3>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h2 class="text-primary"><?php echo $property_stats['total_properties'] ?? '500+'; ?></h2>
                                <p>Total Properties</p>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-success"><?php echo $property_stats['featured_properties'] ?? '25+'; ?></h2>
                                <p>Featured Properties</p>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-info"><?php echo $property_stats['new_listings'] ?? '10+'; ?></h2>
                                <p>New Listings</p>
                            </div>
                            <div class="col-6 mb-3">
                                <h2 class="text-warning"><?php echo $property_stats['avg_price_per_sqft'] ?? '₹4500'; ?></h2>
                                <p>Avg Price/sq.ft</p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <a href="<?php echo BASE_URL; ?>properties" class="btn btn-primary btn-lg">View All Properties</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
