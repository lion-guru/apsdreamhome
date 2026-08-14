<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'How It Works', 'url' => '']];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">How It Works</h1>
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <?php if (isset($crumb['url']) && $crumb['url']): ?>
                                <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= $crumb['title'] ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>

                <p class="lead text-muted text-center mb-5">Your dream property journey in 6 simple steps</p>

                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 mx-auto" class="style-89554">
                                <i class="fas fa-search fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">1. Explore</h5>
                            <p class="text-muted mb-0">Browse our curated collection of verified properties across Gorakhpur, Lucknow, and UP. Use filters for budget, location, and property type.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 mx-auto" class="style-89554">
                                <i class="fas fa-calendar-check fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">2. Schedule Visit</h5>
                            <p class="text-muted mb-0">Book a free site visit at your convenience. Our property advisor will guide you through the colony and show you the best plots available.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 mx-auto" class="style-89554">
                                <i class="fas fa-file-contract fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">3. Verify Documents</h5>
                            <p class="text-muted mb-0">Our legal team verifies all property documents â€” title, encumbrance certificate, RERA registration, and government approvals.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3 mx-auto" class="style-89554">
                                <i class="fas fa-handshake fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">4. Book Your Plot</h5>
                            <p class="text-muted mb-0">Reserve your chosen plot with a small booking amount. Get instant confirmation and booking receipt via SMS and email.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3 mx-auto" class="style-89554">
                                <i class="fas fa-credit-card fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">5. Easy Payments</h5>
                            <p class="text-muted mb-0">Choose flexible payment options â€” full payment, easy EMI, or construction-linked plans. Set up auto-pay with NACH/e-Mandate.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                            <div class="rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center mb-3 mx-auto" class="style-89554">
                                <i class="fas fa-key fa-lg"></i>
                            </div>
                            <h5 class="fw-bold">6. Get Possession</h5>
                            <p class="text-muted mb-0">After complete payment and registry, receive your plot with all documents. Welcome to your dream property!</p>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-5">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4 text-primary text-center">Useful Tools</h4>
                        <div class="row g-3 text-center">
                            <div class="col-6 col-md-3">
                                <a href="<?= BASE_URL ?>/stamp-duty-calculator" class="text-decoration-none">
                                    <div class="p-3 rounded-3 bg-light">
                                        <i class="fas fa-calculator text-primary fa-2x mb-2"></i>
                                        <h6 class="fw-bold text-dark mb-0">Stamp Duty</h6>
                                        <small class="text-muted">Calculator</small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-md-3">
                                <a href="<?= BASE_URL ?>/plot-converter" class="text-decoration-none">
                                    <div class="p-3 rounded-3 bg-light">
                                        <i class="fas fa-ruler-combined text-primary fa-2x mb-2"></i>
                                        <h6 class="fw-bold text-dark mb-0">Plot Size</h6>
                                        <small class="text-muted">Converter</small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-md-3">
                                <a href="<?= BASE_URL ?>/property-valuation" class="text-decoration-none">
                                    <div class="p-3 rounded-3 bg-light">
                                        <i class="fas fa-chart-line text-primary fa-2x mb-2"></i>
                                        <h6 class="fw-bold text-dark mb-0">Property</h6>
                                        <small class="text-muted">Valuation</small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-md-3">
                                <a href="<?= BASE_URL ?>/home-loan-eligibility" class="text-decoration-none">
                                    <div class="p-3 rounded-3 bg-light">
                                        <i class="fas fa-university text-primary fa-2x mb-2"></i>
                                        <h6 class="fw-bold text-dark mb-0">Home Loan</h6>
                                        <small class="text-muted">Eligibility</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
