<!-- Hero Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-search me-3"></i>Buy Property</h1>
        <p class="lead">Find your dream property from our verified listings</p>
    </div>
</section>

<!-- Search Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <h4 class="mb-4 text-center">Search Properties</h4>
                        <form action="<?php echo BASE_URL; ?>/properties" method="GET">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <select name="type" class="form-select">
                                        <option value="">Property Type</option>
                                        <option value="residential">Residential Plot</option>
                                        <option value="house">House/Villa</option>
                                        <option value="flat">Flat/Apartment</option>
                                        <option value="commercial">Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="location" class="form-select">
                                        <option value="">Select Location</option>
                                        <option value="Gorakhpur">Gorakhpur</option>
                                        <option value="Lucknow">Lucknow</option>
                                        <option value="Kushinagar">Kushinagar</option>
                                        <option value="Varanasi">Varanasi</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="budget" class="form-select">
                                        <option value="">Budget Range</option>
                                        <option value="under_5l">Under ₹5 Lakhs</option>
                                        <option value="5_10l">₹5 - 10 Lakhs</option>
                                        <option value="10_20l">₹10 - 20 Lakhs</option>
                                        <option value="20_50l">₹20 - 50 Lakhs</option>
                                        <option value="above_50l">Above ₹50 Lakhs</option>
                                    </select>
                                </div>
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-search me-2"></i>Search Properties
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Listings -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-4">Featured Properties for Sale</h3>
        <div class="row">
            <?php if (!empty($featured_properties)): ?>
                <?php foreach (array_slice($featured_properties, 0, 3) as $project): 
                    $slug = $project['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project['title']));
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-home fa-3x text-primary mb-3"></i>
                            <h5><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($project['location']); ?></p>
                            <p class="h5 text-primary"><?php echo $project['price']; ?></p>
                            <a href="<?php echo BASE_URL; ?>/projects/<?php echo $slug; ?>" class="btn btn-outline-primary mt-2">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No properties available at the moment. <a href="<?php echo BASE_URL; ?>/list-property">Post your property</a> for free!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 text-center text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <h3>Want to sell your property?</h3>
        <p class="mb-4">List your property with us and reach thousands of buyers</p>
        <a href="<?php echo BASE_URL; ?>/sell" class="btn btn-warning btn-lg">Post Your Property</a>
    </div>
</section>
