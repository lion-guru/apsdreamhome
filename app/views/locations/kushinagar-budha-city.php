<?php
/**
 * Budha City Kushinagar Project View - APS Dream Homes
 * Migrated from resources/views/Views/budhacity.php
 */

// init.php not found â€” helpers.php already loaded from bootstrap

// --- Dynamic Data Section ---
$amenities = [];

// 1. Try to fetch amenities from DB
try {
    if (class_exists('\App\Core\App')) {
        $db = \App\Core\App::database();
        $result = $db->fetchAll("SELECT title, image, alt_text FROM amenities_kushinagar ORDER BY id ASC");
        if ($result) {
            $amenities = $result;
        }
    }
} catch (\Exception $e) { error_log('kushinagar-budha-city amenities fetch: ' . $e->getMessage()); }

// Fallback static amenities
if (empty($amenities)) {
    for ($i = 1; $i <= 4; $i++) {
        $amenities[] = [
            'title' => 'Top Amenity ' . $i,
            'image' => 'amenities/' . $i . '.jpg',
            'alt_text' => 'Budha City Amenity ' . $i
        ];
    }
}

$page_title = 'Budha City Kushinagar | APS Dream Homes';

ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <!-- Hero Banner -->
        <div class="page-banner mb-5" class="style-22214">
            <div class="container text-center">
                <h1 class="display-3 fw-bold mb-3 animate-fade-up">Budha City</h1>
                <p class="lead animate-fade-up">Integrated Township at Premwaliya, Kushinagar Highway</p>
            </div>
        </div>

        <div class="container">
            <!-- Project Overview -->
            <div class="row align-items-center mb-5 pb-4">
                <div class="col-lg-7">
                    <div class="pe-lg-5">
                        <h2 class="display-6 fw-bold text-primary mb-4">Kushinagar Projects</h2>
                        <h4 class="fw-bold mb-3">Overview - Budha City</h4>
                        <p class="lead text-dark mb-4">
                            <strong>BUDHA CITY</strong> is an integrated township located at a Prime Location in Kushinagar, 
                            situated right on the Premwaliya Kushinagar Highway.
                        </p>
                        <p class="text-muted mb-4">
                            Spread across more than 15 acres, the township is being developed in organized blocks. 
                            It features a diverse range of offerings including <strong>Plots, Row Houses, and Commercial spaces</strong>. 
                            The infrastructure is designed for modern living with an Entrance Gate, 24/7 Electricity, 
                            advanced Drainage system, and a dedicated Shopping Centre.
                        </p>
                        
                        <div class="bg-light p-4 rounded-4 mb-4 border-start border-primary border-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-location-arrow text-primary me-2"></i>Prime Connectivity</h5>
                            <p class="small text-muted mb-0">
                                Our township is strategically located near a maximum number of Engineering Colleges, 
                                Educational Institutions, Dental Colleges, Railway Station, Petrol Pumps, Airport, 
                                Service Stations, Banks, ATMs, and City Mall.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 mt-5 mt-lg-0">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid" alt="Budha City Kushinagar" />
                        <div class="card-body bg-white text-center py-3">
                            <span class="fw-bold text-primary"><i class="fas fa-gem me-2"></i>Premium Development in Kushinagar</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities Section -->
            <div class="py-5 mb-5">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Top Amenities</h2>
                    <p class="text-muted">Experience the best-in-class facilities at Budha City</p>
                </div>
                <div class="row g-4">
                    <?php foreach ($amenities as $amenity): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden hover-lift h-100">
                                <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="<?= h($amenity['alt_text']) ?>" class="style-12213">
                                <div class="card-body text-center p-3">
                                    <h6 class="fw-bold mb-0"><?= h($amenity['title']) ?></h6>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="card border-0 bg-primary text-white rounded-5 p-5 text-center mb-5 shadow-lg position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 p-4 opacity-10">
                    <i class="fas fa-city fa-10x"></i>
                </div>
                <div class="position-relative z-index-1">
                    <h2 class="display-5 fw-bold mb-3">Invest in Your Future at Budha City</h2>
                    <p class="lead mb-4 opacity-75">Secure your plot or row house in Kushinagar's most promising integrated township.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="tel:+917007444842" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary">
                            <i class="fas fa-phone-alt me-2"></i>Call for Booking
                        </a>
                        <a href="/contact" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold">
                            Get Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo $content;
