<?php
$page_title = 'Astrology & Property - APS Dream Home';
$page_description = 'Explore how Vedic astrology influences property selection and investment timing. Get personalized astrological guidance for your dream home.';
?>
<!-- Hero Section -->
<section class="hero-section text-white text-center py-5" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?= get_asset_url('assets/images/astrology-hero.jpg') ?>'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">Astrology & Property</h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Align your property investment with cosmic energies and planetary positions.
                </p>
                <div class="d-flex gap-3 mt-4 justify-content-center">
                    <a href="<?= BASE_URL ?>/contact" class="btn btn-light btn-lg px-4 py-3">
                        <i class="fas fa-phone me-2"></i>Consult an Astrologer
                    </a>
                    <a href="<?= BASE_URL ?>/colonies" class="btn btn-outline-light btn-lg px-4 py-3">
                        <i class="fas fa-building me-2"></i>Explore Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('nav.menu.home') ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Astrology & Property</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Introduction -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="mb-4">Vedic Astrology for Property Selection</h2>
                <p class="lead text-muted mb-4">In Vedic astrology, the placement of planets at the time of property purchase significantly influences prosperity and harmony.</p>
                <p>The key astrological factors include the Lagna (ascendant), the 4th house (home & mother), the 2nd house (wealth), and the strength of benefic planets like Jupiter, Venus, and the Moon.</p>
                <p>Choosing an auspicious time (muhurat) for property transactions can enhance positive outcomes and minimize obstacles.</p>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="100">
                <img src="<?= get_asset_url('assets/images/astrology.jpg') ?>" alt="Astrology & Property" class="img-fluid rounded-4 shadow">
            </div>
        </div>
    </div>
</section>

<!-- Key Factors -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title mb-3">Astrological Property Factors</h2>
            <p class="section-subtitle">Key planetary influences to consider when selecting property</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3" style="width:70px;height:70px;border-radius:50%;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-star display-5"></i>
                        </div>
                        <h4>Lagna (Ascendant)</h4>
                        <p class="text-muted">The rising sign at property purchase time determines property fortune and ownership benefits.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3" style="width:70px;height:70px;border-radius:50%;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-home display-5"></i>
                        </div>
                        <h4>4th House</h4>
                        <p class="text-muted">The 4th house represents home, mother, and domestic happiness. A strong 4th house brings peace to the property.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3" style="width:70px;height:70px;border-radius:50%;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-gem display-5"></i>
                        </div>
                        <h4>Jupiter (Guru)</h4>
                        <p class="text-muted">Jupiter's strength blesses property with wealth, growth, and long-term appreciation.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lucky Directions -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="mb-4">Lucky Directions by Zodiac Sign</h2>
                <p class="text-muted mb-4">Different zodiac signs have varying lucky directions for property placement and orientation.</p>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Rashi (Sign)</th>
                                <th>Lucky Direction</th>
                                <th>Best Property Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Mesha (Aries)</td><td>North, East</td><td>Independent house</td></tr>
                            <tr><td>Vrishabha (Taurus)</td><td>South, West</td><td>Apartment</td></tr>
                            <tr><td>Mithuna (Gemini)</td><td>North, West</td><td>Villa</td></tr>
                            <tr><td>Cancer</td><td>North, East</td><td>Independent house</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3">Auspicious Property Timings</h4>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item bg-transparent border-0 py-2">
                                <i class="fas fa-check-circle text-success me-2"></i>Bhava Pratirupa (Venus in 1st/4th/7th/10th)
                            </li>
                            <li class="list-group-item bg-transparent border-0 py-2">
                                <i class="fas fa-check-circle text-success me-2"></i>Dhan Yoga active in birth chart
                            </li>
                            <li class="list-group-item bg-transparent border-0 py-2">
                                <i class="fas fa-check-circle text-success me-2"></i>Benefic transits (Jupiter, Venus, Mercury)
                            </li>
                            <li class="list-group-item bg-transparent border-0 py-2">
                                <i class="fas fa-check-circle text-success me-2"></i>Avoid Saturn/Ketu/Rahu periods for purchase
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Recommendations -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title mb-3">Astrology-Friendly Properties</h2>
            <p class="section-subtitle">Our properties selected for optimal planetary alignment</p>
        </div>
        <div class="row g-4">
            <?php if (isset($colonies) && !empty($colonies)): ?>
                <?php foreach ($colonies as $colony): ?>
                <div class="col-md-6 col-lg-3" data-aos="fade-up">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden">
                        <div class="position-relative">
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($colony['image_path'] ?? 'assets/images/default-banner.jpg') ?>" alt="<?= htmlspecialchars($colony['name']) ?>" class="card-img-top" style="height:180px;object-fit:cover">
                            <span class="badge bg-info text-dark position-absolute top-0 end-0 m-2">Astrology OK</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($colony['name'] ?? '') ?></h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($colony['location'] ?? '') ?></p>
                            <p class="fw-bold text-primary mb-0"><?= !empty($colony['starting_price']) ? '₹' . number_format($colony['starting_price']) : 'Contact Us' ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 text-center">
                            <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($colony['slug'] ?? $colony['name'] ?? '') ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Check back soon for astrology-aligned properties.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3">Get Your Personal Astrology & Property Consultation</h2>
                <p class="lead mb-4">Our expert astrologers provide personalized guidance for property selection and investment timing.</p>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-light btn-lg px-5 py-3">
                    <i class="fas fa-calendar-check me-2"></i>Book Consultation
                </a>
            </div>
        </div>
    </div>
</section>