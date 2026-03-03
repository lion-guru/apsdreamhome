<?php
/**
 * Lucknow Ram Nagri Project View - APS Dream Homes
 */
?>

<!-- Page Header -->
<section class="project-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Ram Nagri - Lucknow</h1>
        <p class="lead mb-0">Premium Integrated Township at Haidergarh Highway</p>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2 mb-5">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $crumb['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<div class="container">
            <div class="row mb-5">
                <div class="col-lg-8" data-aos="fade-right">
                    <h2 class="text-primary fw-bold mb-4">About RAM NAGRI</h2>
                    <p class="lead text-muted mb-4">
                        <b>RAM NAGRI</b> is situated at the prime location of Haidergarh Highway. 
                        Developed by APS Dream Homes, it is an integrated township that offers a range of residential 
                        and commercial opportunities.
                    </p>
                    <p>
                        The township is spread across more than 15 acres and is being developed in organized blocks. 
                        It features:
                    </p>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Residential Plots & Row Houses</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Commercial Spaces</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Grand Entrance Gate</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Modern Drainage System & Electricity</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Shopping Centre & Community Parks</li>
                    </ul>
                    <div class="alert alert-info border-0 shadow-sm p-4 rounded-4">
                        <h5 class="fw-bold mb-2"><i class="fas fa-map-marker-alt me-2"></i>Strategic Location</h5>
                        Our township is located nearby premium educational institutions, engineering & dental colleges, 
                        railway station, petrol pumps, airport, and city malls, ensuring a convenient lifestyle for your family.
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
                        <h4 class="fw-bold text-primary mb-3">Quick Inquiry</h4>
                        <form action="<?= BASE_URL ?>contact" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="subject" value="Inquiry about Ram Nagri Lucknow">
                            <div class="mb-3">
                                <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                            </div>
                            <div class="mb-3">
                                <textarea name="message" class="form-control" rows="3" placeholder="I am interested in Ram Nagri..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Submit Inquiry</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="py-5">
                <h3 class="text-center fw-bold mb-5">Top Amenities</h3>
                <div class="row g-4 text-center">
                    <?php if (!empty($amenities)): ?>
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="col-md-3 col-6" data-aos="zoom-in">
                                <div class="card border-0 shadow-sm rounded-4 p-3 hover-lift h-100">
                                    <div class="mb-3">
                                        <img src="<?= get_asset_url($amenity['image'], 'images') ?>" class="img-fluid rounded-3" alt="<?= $amenity['alt_text'] ?>" onerror="this.src='https://placehold.co/100x100?text=Amenity'">
                                    </div>
                                    <p class="mb-0 fw-bold"><?= h($amenity['title']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php for($i=1; $i<=4; $i++): ?>
                            <div class="col-md-3 col-6">
                                <div class="card border-0 shadow-sm rounded-4 p-3 hover-lift">
                                    <img src="<?= get_asset_url("amenities/$i.jpg", 'images') ?>" class="img-fluid rounded-3 mb-3" alt="Amenity <?= $i ?>" onerror="this.src='https://placehold.co/100x100?text=Amenity'">
                                    <p class="mb-0 fw-bold">Amenity <?= $i ?></p>
                                </div>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .project-hero-section {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }
    .hover-lift {
        transition: all 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>
