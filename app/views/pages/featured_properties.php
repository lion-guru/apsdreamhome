<?php
// Featured Properties Page - APS Dream Homes

$featuredProperties = [
    ['name' => 'APS Anant City - Premium Plots', 'location' => 'Gorakhpur', 'price' => '12,50,000', 'area' => '1200 sq.ft', 'type' => 'Residential Plot', 'image' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400'],
    ['name' => 'Suyoday Colony Phase 2', 'location' => 'Gorakhpur', 'price' => '8,75,000', 'area' => '1000 sq.ft', 'type' => 'Residential Plot', 'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400'],
    ['name' => 'Raghunath Nagri Premium', 'location' => 'Gorakhpur', 'price' => '15,00,000', 'area' => '1500 sq.ft', 'type' => 'Residential Plot', 'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400'],
    ['name' => 'Braj Radha Nagri', 'location' => 'Mathura', 'price' => '10,00,000', 'area' => '1100 sq.ft', 'type' => 'Residential Plot', 'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400'],
    ['name' => 'Awadhpuri Extension', 'location' => 'Gorakhpur', 'price' => '9,50,000', 'area' => '1050 sq.ft', 'type' => 'Residential Plot', 'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=400'],
    ['name' => 'Budh Bihari Colony', 'location' => 'Gorakhpur', 'price' => '7,50,000', 'area' => '900 sq.ft', 'type' => 'Residential Plot', 'image' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=400'],
];
?>

<section class="py-5 bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Featured Properties</h1>
        <p class="lead">Handpicked premium properties for you</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <?php foreach ($featuredProperties as $property): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="position-relative">
                        <img src="<?= $property['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($property['name']) ?>" style="height:220px;object-fit:cover;">
                        <span class="badge bg-success position-absolute top-0 start-0 m-3">Featured</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($property['name']) ?></h5>
                        <p class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($property['location']) ?></p>
                        <div class="d-flex gap-3 mb-3">
                            <span><i class="fas fa-ruler-combined text-muted me-1"></i><?= $property['area'] ?></span>
                            <span><i class="fas fa-tag text-muted me-1"></i><?= $property['type'] ?></span>
                        </div>
                        <div class="h4 text-primary">₹<?= $property['price'] ?></div>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3">
                        <a href="/apsdreamhome/properties" class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
