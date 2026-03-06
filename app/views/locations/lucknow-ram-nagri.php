<?php
/**
 * Lucknow Ram Nagri Project View - APS Dream Homes
 * Migrated from resources/views/Views/lucknow-ram-nagri.php
 */

require_once __DIR__ . '/init.php';

$page_title = 'Ram Nagri Lucknow | APS Dream Homes';
$layout = 'modern';

ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="page-banner mb-5" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?= get_asset_url('banner/lucknow-banner.jpg', 'images') ?>') center/cover; padding: 100px 0; color: #fff; border-radius: 20px;">
            <div class="container text-center">
                <h1 class="display-4 fw-bold animate-fade-up">Ram Nagri - Lucknow</h1>
                <p class="lead animate-fade-up">Premium Integrated Township at Haidergarh Highway</p>
            </div>
        </div>

        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8">
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
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
                        <h4 class="fw-bold text-primary mb-3">Quick Inquiry</h4>
                        <form action="<?= BASE_URL ?>contact" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="subject" value="Inquiry about Ram Nagri">
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
                    <?php for($i=1; $i<=7; $i++): ?>
                        <div class="col-md-3 col-6">
                            <div class="card border-0 shadow-sm rounded-4 p-3 hover-lift">
                                <img src="<?= get_asset_url("amenities/$i.jpg", 'images') ?>" class="img-fluid rounded-3 mb-3" alt="Amenity <?= $i ?>">
                                <p class="mb-0 fw-bold">Amenity <?= $i ?></p>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
